<?php

namespace SaasOvation\Common\Test\Event;

use DateTimeImmutable;
use DateTimeInterface;
use SaasOvation\Common\Domain\Model\DomainEvent;
use SaasOvation\Common\Domain\Model\ImplementsDomainEvent;

class TestableNavigableDomainEvent implements DomainEvent
{
    use ImplementsDomainEvent;

    /**
     * @var TestableDomainEvent
     */
    private $nestedEvent;

    public function __construct($anId, $aName)
    {
        $this->setNestedEvent(new TestableDomainEvent($anId, $aName));
        $this->setOccurredOn(new DateTimeImmutable());
    }

    public function eventVersion()
    {
        return $this->eventVersion;
    }

    public function nestedEvent()
    {
        return $this->nestedEvent;
    }

    public function occurredOn()
    {
        return $this->occurredOn;
    }

    private function setNestedEvent(TestableDomainEvent $nestedEvent)
    {
        $this->nestedEvent = $nestedEvent;
    }

    private function setOccurredOn(DateTimeInterface $occurredOn)
    {
        $this->occurredOn = $occurredOn;
    }
}
