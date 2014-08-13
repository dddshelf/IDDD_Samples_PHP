<?php

namespace SaasOvation\IdentityAccess\Application;

use SaasOvation\Common\Domain\Model\DomainEvent;
use SaasOvation\Common\Domain\Model\DomainEventSubscriber;
use SaasOvation\Common\Event\EventStore;

class GenericDomainEventSubscriber implements DomainEventSubscriber
{
    /**
     * @var EventStore
     */
    private $eventStore;

    public function __construct($eventStore)
    {
        $this->eventStore = $eventStore;
    }

    public function handleEvent(DomainEvent $aDomainEvent)
    {
        $this->store($aDomainEvent);
    }

    public function subscribedToEventType()
    {
        return DomainEvent::class; // all domain events
    }

    /**
     * Stores aDomainEvent to the event store.
     *
     * @param DomainEvent $aDomainEvent The DomainEvent to store
     */
    private function store(DomainEvent $aDomainEvent)
    {
        $this->eventStore()->append($aDomainEvent);
    }

    /**
     * Answers my EventStore.
     *
     * @return EventStore
     */
    private function eventStore()
    {
        return $this->eventStore;
    }
}