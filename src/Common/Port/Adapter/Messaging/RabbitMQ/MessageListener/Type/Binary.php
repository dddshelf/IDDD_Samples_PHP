<?php

namespace SaasOvation\Common\Port\Adapter\Messaging\RabbitMQ\MessageListener\Type;

use SaasOvation\Common\Port\Adapter\Messaging\RabbitMQ\MessageListener\Type;

class Binary extends Type
{
    public function isBinaryListener()
    {
        return true;
    }
}
