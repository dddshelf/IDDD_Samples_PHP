<?php

namespace SaasOvation\Common\Port\Adapter\Messaging\RabbitMQ\MessageListener;

abstract class Type
{
    public function isBinaryListener()
    {
        return false;
    }

    public function isTextListener()
    {
        return false;
    }
}
