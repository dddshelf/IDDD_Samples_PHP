<?php

namespace SaasOvation\Common\Test\Event;

use DateTimeImmutable;
use SaasOvation\Common\Domain\Model\DomainEvent;
use SaasOvation\Common\Domain\Model\ImplementsDomainEvent;

class AnotherTestableDomainEvent implements DomainEvent
{
    use ImplementsDomainEvent;

    /**
     * @var int
     */
    private $value;

    public function __construct($aValue)
    {
        $this->setEventVersion(1);
        $this->setOccurredOn(new DateTimeImmutable());
        $this->setValue($aValue);
    }

    public function value()
    {
        return $this->value;
    }

    private function setEventVersion($eventVersion)
    {
        $this->eventVersion = $eventVersion;
    }

    private function setOccurredOn(DateTimeImmutable $occurredOn)
    {
        $this->occurredOn = $occurredOn;
    }

    private function setValue($value)
    {
        $this->value = $value;
    }
}
