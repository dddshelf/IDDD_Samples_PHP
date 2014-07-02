<?php

namespace SaasOvation\Common\Domain\Model;

use DateTimeInterface;

trait ImplementsDomainEvent
{
    /**
     * @var int
     */
    private $eventVersion = 1;

    /**
     * @var DateTimeInterface
     */
    private $occurredOn;

    public function eventVersion()
    {
        return $this->eventVersion;
    }

    public function occurredOn()
    {
        return $this->occurredOn;
    }
}