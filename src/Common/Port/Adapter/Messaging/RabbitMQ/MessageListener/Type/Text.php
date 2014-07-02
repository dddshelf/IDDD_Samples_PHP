<?php

namespace SaasOvation\Common\Port\Adapter\Messaging\RabbitMQ\MessageListener\Type;

use SaasOvation\Common\Port\Adapter\Messaging\RabbitMQ\MessageListener\Type;

class Text extends Type
{
    public function isTextListener()
    {
        return true;
    }
}
