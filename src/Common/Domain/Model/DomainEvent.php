<?php

namespace SaasOvation\Common\Domain\Model;

use DateTimeInterface;

interface DomainEvent
{
    /**
     * @return int
     */
    public function eventVersion();

    /**
     * @return DateTimeInterface
     */
    public function occurredOn();
}
