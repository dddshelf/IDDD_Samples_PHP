<?php

namespace SaasOvation\Common\Event\Sourcing;

interface EventNotifiable
{
    public function notifyDispatchableEvents();
}
