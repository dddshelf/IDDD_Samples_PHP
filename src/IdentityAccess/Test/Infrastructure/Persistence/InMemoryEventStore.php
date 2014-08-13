<?php

namespace SaasOvation\IdentityAccess\Test\Infrastructure\Persistence;

use Doctrine\Common\Collections\ArrayCollection;
use SaasOvation\Common\Domain\Model\DomainEvent;
use SaasOvation\Common\Event\EventSerializer;
use SaasOvation\Common\Event\EventStore;
use SaasOvation\Common\Event\StoredEvent;
use SaasOvation\Common\Persistence\CleanableStore;

class InMemoryEventStore implements EventStore, CleanableStore
{
    private $storedEvents;

    public function __construct()
    {
        $this->storedEvents = new ArrayCollection();
    }

    public function allStoredEventsBetween($aLowStoredEventId, $aHighStoredEventId)
    {
        $events = new ArrayCollection();

        foreach ($this->storedEvents as $storedEvent) {
            if ($storedEvent->eventId() >= $aLowStoredEventId && $storedEvent->eventId() <= $aHighStoredEventId) {
                $events->add($storedEvent);
            }
        }

        return $events;
    }

    public function allStoredEventsSince($aStoredEventId)
    {
        $events = new ArrayCollection();

        foreach ($this->storedEvents as $storedEvent) {
            if ($storedEvent->eventId() > $aStoredEventId) {
                $events->add($storedEvent);
            }
        }

        return $events;
    }

    public function append(DomainEvent $aDomainEvent)
    {
        $eventSerialization = EventSerializer::instance()->serialize($aDomainEvent);

        $storedEvent = new StoredEvent(
            get_class($aDomainEvent),
            $aDomainEvent->occurredOn(),
            $eventSerialization,
            $this->storedEvents->count() + 1
        );

        $this->storedEvents->add($storedEvent);

        return $storedEvent;
    }

    public function close()
    {
        $this->clean();
    }

    public function countStoredEvents()
    {
        return $this->storedEvents->count();
    }

    public function clean()
    {
        $this->storedEvents = new ArrayCollection();
    }
}
