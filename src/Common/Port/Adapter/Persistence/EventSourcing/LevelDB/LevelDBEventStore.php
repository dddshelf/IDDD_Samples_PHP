<?php

namespace SaasOvation\Common\Port\Adapter\Persistence\EventSourcing\LevelDB;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Exception;
use InvalidArgumentException;
use SaasOvation\Common\Event\EventSerializer;
use SaasOvation\Common\Event\Sourcing\DispatchableDomainEvent;
use SaasOvation\Common\Event\Sourcing\EventNotifiable;
use SaasOvation\Common\Event\Sourcing\EventStore;
use SaasOvation\Common\Event\Sourcing\EventStoreException;
use SaasOvation\Common\Event\Sourcing\EventStreamId;
use SaasOvation\Common\Port\Adapter\Persistence\EventSourcing\DefaultEventStream;

/**
 * I am an EventStore for LevelDB. I am a pure Java implementation
 * using the org.iq80 (Dain Sundstrom) implementation of LevelDB.
 *
 * @author Vaughn Vernon
 */
class LevelDBEventStore implements EventStore
{
    /**
     * @var LevelDBEventStore
     */
    private static $instance;

    /**
     * @var EventNotifiable
     */
    private $eventNotifiable;

    /**
     * @var LevelDBJournal
     */
    private $journal;

    /**
     * @var EventSerializer
     */
    private $serializer;

    public static function instance($aDirectoryPath)
    {
        if (null === static::$instance) {
            static::$instance = new LevelDBEventStore($aDirectoryPath);
        } else {
            // normally unnecessary, but tests close the journal
            static::$instance->setJournal(LevelDBJournal::initializeInstance($aDirectoryPath));
        }

        return static::$instance;
    }

    public function appendWith(EventStreamId $aStartingIdentity, Collection $anEvents)
    {
        $entries = [];

        $keyProvider = new StreamKeyProvider($aStartingIdentity->streamName(), $aStartingIdentity->streamVersion());

        $entryIndex = 0;

        foreach ($anEvents as $event) {

            $streamKey = $keyProvider->nextReferenceKey();

            $eventValue = $this->journal()->valueWithMetadata(
                $this->serializer()->serialize($event),
                get_class($event)
            );

            $entries[$entryIndex++] = new LoggableJournalEntry(
                $eventValue,
                $streamKey,
                $keyProvider->primaryResourceName()
            );
        }

        $this->journal()->logEntries($entries);

        $this->notifyDispatchableEvents();
    }

    public function close()
    {
        $this->journal()->close();
    }

    public function eventsSince($aLastReceivedEvent)
    {
        $events = null;

        try {
            $entries = $this->journal()->loggedJournalEntriesSince($aLastReceivedEvent);

            $events = $this->toDispatchableDomainEvents($entries);

        } catch (Exception $t) {
            throw new EventStoreException(
                'Cannot query event store for events since: ' . $aLastReceivedEvent . ' because: ' . $t->getMessage(),
                $t->getCode(),
                $t
            );
        }

        return $events;
    }

    public function eventStreamSince(EventStreamId $anIdentity)
    {
        $events = null;

        try {
            $keyProvider = new StreamKeyProvider($anIdentity->streamName(), $anIdentity->streamVersion());

            $entries = $this->journal()->referencedLoggedJournalEntries($keyProvider);

            $events = $this->toDomainEvents($entries);

            if ($events->isEmpty()) {
                throw new EventStoreException(
                    'There is no such event stream: ' . $anIdentity->streamName() . ' : ' . $anIdentity->streamVersion()
                );
            }

            $entry = $entries->get($entries->count() - 1);

            $streamVersion = $keyProvider->lastKeyPart($entry->referenceKey());

            $version = intval($streamVersion);

        } catch (Exception $t) {
            throw new EventStoreException(
                'Cannot query event stream for: ' . $anIdentity->streamName() . ' since version: ' . $anIdentity->streamVersion() . ' because: ' . $t->getMessage(),
                $t->getCode(),
                $t
            );
        }

        return new DefaultEventStream($events, $version);
    }

    public function fullEventStreamFor(EventStreamId $anIdentity)
    {
        return $this->eventStreamSince($anIdentity->withStreamVersion(1));
    }

    public function purge()
    {
        $this->journal()->purge();
    }

    public function registerEventNotifiable(EventNotifiable $anEventNotifiable)
    {
        $this->eventNotifiable = $anEventNotifiable;
    }

    private function __construct($aDirectoryPath)
    {
        $this->setJournal(LevelDBJournal::initializeInstance($aDirectoryPath));
        $this->setSerializer(EventSerializer::instance());
    }

    private function eventNotifiable()
    {
        return $this->eventNotifiable;
    }

    private function journal()
    {
        return $this->journal;
    }

    private function setJournal(LevelDBJournal $aJournal)
    {
        $this->journal = $aJournal;
    }

    private function notifyDispatchableEvents()
    {
        $eventNotifiable = $this->eventNotifiable();

        if (null !== $eventNotifiable) {
            $this->eventNotifiable()->notifyDispatchableEvents();
        }
    }

    private function serializer()
    {
        return $this->serializer;
    }

    private function setSerializer(EventSerializer $aSerializer)
    {
        $this->serializer = $aSerializer;
    }

    private function toDomainEvents(Collection $anEntries)
    {
        $events = new ArrayCollection();

        foreach ($anEntries as $entry) {

            $eventClassName = $entry->nextMetadataValue();

            $eventBody = $entry->value();

            $domainEvent = $this->serializer()->deserialize($eventBody, $eventClassName);

            $events->add($domainEvent);
        }

        return $events;
    }

    /**
     * @param $anEntries
     * @return Collection
     */
    private function toDispatchableDomainEvents($anEntries)
    {
        $events = new ArrayCollection();

        foreach ($anEntries as $entry) {

            $eventClassName = $entry->nextMetadataValue();

            $eventBody = $entry->value();

            $domainEvent = $this->serializer()->deserialize($eventBody, $eventClassName);

            $events->add(new DispatchableDomainEvent($entry->journalSequence(), $domainEvent));
        }

        return $events;
    }
}
