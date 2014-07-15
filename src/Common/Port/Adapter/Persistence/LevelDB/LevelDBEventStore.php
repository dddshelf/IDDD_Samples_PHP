<?php

namespace SaasOvation\Common\Port\Adapter\Persistence\LevelDB;

use Doctrine\Common\Collections\ArrayCollection;
use SaasOvation\Common\Domain\Model\DomainEvent;
use SaasOvation\Common\Event\EventSerializer;
use SaasOvation\Common\Event\EventStore;
use SaasOvation\Common\Event\StoredEvent;

class LevelDBEventStore extends AbstractLevelDBRepository implements EventStore
{
    public static $PRIMARY = 'ES_EVT_PK:';
    public static $INTERNAL_EVENT_ID = 'ES_EVT_EID';

    private $storedEventIdSequence = 0;

    public function __construct($aDirectoryPath)
    {
        parent::__construct($aDirectoryPath);

        $this->prepareDatabase();
    }

    public function allStoredEventsBetween($aLowStoredEventId, $aHighStoredEventId)
    {
        $storedEvents = new ArrayCollection();

        $uow = LevelDBUnitOfWork::readOnly($this->database());

        $done = false;

        for ($idSequence = $aLowStoredEventId; !$done && $idSequence <= $aHighStoredEventId; ++$idSequence) {
            $storedEvent = $uow->readObjectFromString(
                static::$PRIMARY . $idSequence,
                StoredEvent::class
            );

            if (null !== $storedEvent) {
                $storedEvents->add($storedEvent);
            } else {
                $done = true;
            }
        }

        return $storedEvents;
    }

    public function allStoredEventsSince($aStoredEventId)
    {
        return $this->allStoredEventsBetween($aStoredEventId + 1, $this->currentStoredEventIdSequence());
    }

    public function append(DomainEvent $aDomainEvent)
    {
        $uow = LevelDBUnitOfWork::start($this->database());

        $eventSerialization = EventSerializer::instance()->serialize($aDomainEvent);

        $storedEvent = new StoredEvent(
            get_class($aDomainEvent),
            $aDomainEvent->occurredOn(),
            $eventSerialization,
            $this->nextStoredEventIdSequence()
        );

        $this->save($storedEvent, $uow);

        return $storedEvent;
    }

    public function close()
    {
        $this->database()->put(static::$INTERNAL_EVENT_ID, (string) $this->currentStoredEventIdSequence());
    }

    public function countStoredEvents()
    {
        return $this->currentStoredEventIdSequence();
    }

    private function cacheStoredEventIdSequence()
    {
        $cached = false;

        $sequenceValue = $this->database()->get(static::$INTERNAL_EVENT_ID);

        if (false !== $sequenceValue) {
            $this->setStoredEventIdSequence($sequenceValue);

            // only a successful close() will save the correct
            // sequence-> a missing sequence on open indicates the
            // need for a repair (unless the event store is empty).

            $this->database()->delete(static::$INTERNAL_EVENT_ID);

            $cached = true;

        } else {
            $this->setStoredEventIdSequence(0);
        }

        return $cached;
    }

    private function currentStoredEventIdSequence()
    {
        return $this->storedEventIdSequence;
    }

    private function nextStoredEventIdSequence()
    {
        return ++$this->storedEventIdSequence;
    }

    private function prepareDatabase()
    {
        if (!$this->cacheStoredEventIdSequence()) {
            $repairTool = new RepairTool($this->database());

            $repairTool->repairEventStore();

            $lastConfirmedKey = $repairTool->lastConfirmedSequence();

            if ($lastConfirmedKey > 0) {
                $this->setStoredEventIdSequence($lastConfirmedKey);
            }
        }
    }

    private function setStoredEventIdSequence($aStoredEventIdSequence)
    {
        $this->storedEventIdSequence = $aStoredEventIdSequence;
    }

    private function save(StoredEvent $aStoredEvent, LevelDBUnitOfWork $aUoW)
    {
        $aUoW->write(static::$PRIMARY . $aStoredEvent->eventId(), $aStoredEvent);
    }
}
