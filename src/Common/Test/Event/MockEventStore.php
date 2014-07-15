<?php

namespace SaasOvation\Common\Test\Event;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use SaasOvation\Common\AssertionConcern;
use SaasOvation\Common\Domain\Model\DomainEvent;
use SaasOvation\Common\Event\EventSerializer;
use SaasOvation\Common\Event\EventStore;
use SaasOvation\Common\Event\StoredEvent;

class MockEventStore extends AssertionConcern implements EventStore
{
    private static $START_ID = 789;

    /**
     * @var Collection
     */
    private $storedEvents;

    public function __construct()
    {
        // always start with at least 21 events
        
        $this->storedEvents = new ArrayCollection();
        
        $numberOfStoredEvents = $this->millisecondWithinSecond() + 1;

        if ($numberOfStoredEvents < 21) {
            $numberOfStoredEvents = 21;
        }

        for ($idx = 0; $idx < $numberOfStoredEvents; ++$idx) {
            $this->storedEvents->add(
                $this->newStoredEvent(self::$START_ID + $idx, $idx + 1)
            );
        }
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
        return $this->allStoredEventsBetween($aStoredEventId + 1, $this->countStoredEvents());
    }

    public function append(DomainEvent $aDomainEvent)
    {
        $eventSerialization = EventSerializer::instance()->serialize($aDomainEvent);

        $storedEvent = new StoredEvent(
            get_class($aDomainEvent),
            $aDomainEvent->occurredOn(),
            $eventSerialization
        );

        $storedEvent->setEventId($this->storedEvents->count() + 1);

        $this->storedEvents->add($storedEvent);

        return $storedEvent;
    }

    public function close()
    {
        // no-op
    }

    public function countStoredEvents()
    {
        return $this->storedEvents->count();
    }

    private function newStoredEvent($domainEventId, $storedEventId)
    {
        $serializer = EventSerializer::instance();

        $event = new TestableDomainEvent($domainEventId, 'name' . $domainEventId);
        $serializedEvent = $serializer->serialize($event);
        $storedEvent = new StoredEvent(get_class($event), $event->occurredOn(), $serializedEvent, $storedEventId);

        return $storedEvent;
    }

    private function millisecondWithinSecond()
    {
        return round(microtime(true) * 1000) % 1000;
    }
}
