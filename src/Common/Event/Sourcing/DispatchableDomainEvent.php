<?php

namespace SaasOvation\Common\Event\Sourcing;

use SaasOvation\Common\Domain\Model\DomainEvent;

class DispatchableDomainEvent
{
    /**
     * @var DomainEvent
     */
    private $domainEvent;

    /**
     * @var int
     */
    private $eventId;

    public function __construct($anEventId, DomainEvent $aDomainEvent)
    {
        $this->domainEvent  = $aDomainEvent;
        $this->eventId      = $anEventId;
    }
    
    public function domainEvent()
    {
        return $this->domainEvent;
    }

    public function eventId()
    {
        return $this->eventId;
    }
}