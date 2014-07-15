<?php

namespace SaasOvation\Common\Port\Adapter\Persistence\EventSourcing\InMemory;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use InvalidArgumentException;
use SaasOvation\Common\Event\Sourcing\DispatchableDomainEvent;
use SaasOvation\Common\Event\Sourcing\EventNotifiable;
use SaasOvation\Common\Event\Sourcing\EventStore;
use SaasOvation\Common\Event\Sourcing\EventStoreAppendException;
use SaasOvation\Common\Event\Sourcing\EventStoreException;
use SaasOvation\Common\Event\Sourcing\EventStream;
use SaasOvation\Common\Event\Sourcing\EventStreamId;
use SaasOvation\Common\Port\Adapter\Persistence\EventSourcing\DefaultEventStream;

class InMemoryEventStore implements EventStore
{
    /**
     * @var array
     */
    private $eventStreams;

    /**
     * @var Collection
     */
    private $events;

    /**
     * @var EventNotifiable
     */
    private $eventNotifiable;

    /**
     * @var InMemoryEventStore
     */
    private static $instance;

    private function __construct()
    {
        $this->eventStreams = [];
        $this->events = new ArrayCollection();
    }

    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new InMemoryEventStore();
        }

        return self::$instance;
    }

    public function appendWith(EventStreamId $aStartingIdentity, Collection $anEvents)
    {
        if (!isset($this->eventStreams[$aStartingIdentity->streamName()])) {
            $this->eventStreams[$aStartingIdentity->streamName()] = [];
        }

        if (isset($this->eventStreams[$aStartingIdentity->streamName()][$aStartingIdentity->streamVersion()])) {
            throw new EventStoreAppendException(sprintf('The version "%s" already exists on the stream "%s".', $aStartingIdentity->streamVersion(), $aStartingIdentity->streamName()));
        }

        $this->eventStreams[$aStartingIdentity->streamName()][$aStartingIdentity->streamVersion()] = [];

        $anArrayOfEvents = $anEvents->toArray();

        $this->eventStreams[$aStartingIdentity->streamName()][$aStartingIdentity->streamVersion()] = array_merge(
            $this->eventStreams[$aStartingIdentity->streamName()][$aStartingIdentity->streamVersion()],
            $anArrayOfEvents
        );

        $this->events = new ArrayCollection(
            array_merge(
                $this->events->toArray(),
                $anArrayOfEvents
            )
        );

        $this->notifyDispatchableEvents();
    }

    public function close()
    {
        return;
    }

    /**
     * @param $aLastReceivedEvent
     *
     * @return Collection
     */
    public function eventsSince($aLastReceivedEvent)
    {
        $events = [];

        if (null === $aLastReceivedEvent) {
            $events = $this->events->toArray();
        } elseif ($this->events->containsKey($aLastReceivedEvent + 1)) {
            $events = $this->events->slice($aLastReceivedEvent + 1);
        }

        return $this->toDispatchableDomainEvents($events);
    }

    private function toDispatchableDomainEvents(array $aDomainEvents)
    {
        $aDispatchableDomainEvents = new ArrayCollection();

        foreach ($aDomainEvents as $anEventId => $aDomainEvent) {
            $aDispatchableDomainEvents->add(
                new DispatchableDomainEvent(
                    $anEventId,
                    $aDomainEvent
                )
            );
        }

        return $aDispatchableDomainEvents;
    }

    /**
     * @param EventStreamId $anIdentity
     * @return EventStream
     */
    public function eventStreamSince(EventStreamId $anIdentity)
    {
        if (!isset($this->eventStreams[$anIdentity->streamName()]) || !isset($this->eventStreams[$anIdentity->streamName()][$anIdentity->streamVersion()])) {
            throw new EventStoreException(sprintf('The version "%s" or the stream "%s" do not exist.', $anIdentity->streamVersion(), $anIdentity->streamName()));
        }

        $aListOfEvents = new ArrayCollection();
        $accumulateEvents = false;
        $version = $anIdentity->streamVersion();

        foreach ($this->eventStreams[$anIdentity->streamName()] as $version => $events) {
            if ($version === $anIdentity->streamVersion()) {
                $accumulateEvents = true;
            }

            if ($accumulateEvents) {
                foreach ($events as $event) {
                    $aListOfEvents->add($event);
                }
            }
        }

        return new DefaultEventStream(
            $aListOfEvents,
            $version
        );
    }

    public function fullEventStreamFor(EventStreamId $anIdentity)
    {
        if (!isset($this->eventStreams[$anIdentity->streamName()])) {
            return;
        }

        $streamVersion = 0;
        $events = new ArrayCollection();

        foreach ($this->eventStreams[$anIdentity->streamName()] as $streamVersion => $aListOfEvents) {
            foreach ($aListOfEvents as $event) {
                $events->add($event);
            }
        }

        return new DefaultEventStream($events, $streamVersion);
    }

    public function purge()
    {
        $this->eventStreams = [];
        $this->events = new ArrayCollection();
    }

    public function registerEventNotifiable(EventNotifiable $anEventNotifiable)
    {
        $this->eventNotifiable = $anEventNotifiable;
    }

    private function eventNotifiable()
    {
        return $this->eventNotifiable;
    }

    private function notifyDispatchableEvents()
    {
        $eventNotifiable = $this->eventNotifiable();

        if (null !== $eventNotifiable) {
            $this->eventNotifiable()->notifyDispatchableEvents();
        }
    }
}