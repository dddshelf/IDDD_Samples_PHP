<?php

namespace SaasOvation\Common\Test\Domain\Model;

use Doctrine\Common\Collections\Collection;
use SaasOvation\Common\Domain\Model\DomainEvent;
use SaasOvation\Common\Domain\Model\DomainEventSubscriber as BaseDomainEventSubscriber;

class DomainEventSubscriber implements BaseDomainEventSubscriber
{
    /**
     * @var Collection
     */
    private $handledEvents;

    function __construct(Collection $events)
    {
        $this->handledEvents = $events;
    }

    public function handleEvent(DomainEvent $aDomainEvent)
    {
        $this->handledEvents->add(get_class($aDomainEvent));
    }

    public function subscribedToEventType()
    {
        return DomainEvent::class;
    }
}