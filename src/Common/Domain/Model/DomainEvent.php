<?php

namespace SaasOvation\Common\Domain\Model;

interface DomainEvent
{
    /**
     * @return int
     */
    public function eventVersion();

    /**
     * @return \DateTime
     */
    public function occurredOn();
}
