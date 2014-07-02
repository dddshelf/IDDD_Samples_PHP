<?php

namespace SaasOvation\Common\Event\Sourcing;

interface EventStream
{
    public function events();

    public function version();
}
