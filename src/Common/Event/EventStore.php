<?php

namespace SaasOvation\Common\Event;

use SaasOvation\Common\Domain\Model\DomainEvent;

interface EventStore
{
    public function allStoredEventsBetween($aLowStoredEventId, $aHighStoredEventId);

    public function allStoredEventsSince($aStoredEventId);

    public function append(DomainEvent $aDomainEvent);

    public function close();

    public function countStoredEvents();
}
