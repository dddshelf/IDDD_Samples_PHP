<?php

namespace SaasOvation\Common\Domain\Model;

interface DomainEventSubscriber
{
    public function handleEvent(DomainEvent $aDomainEvent);

    public function subscribedToEventType();
}
