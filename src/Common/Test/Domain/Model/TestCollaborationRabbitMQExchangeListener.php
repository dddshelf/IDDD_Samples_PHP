<?php

namespace SaasOvation\Common\Test\Domain\Model;

use SaasOvation\Common\Port\Adapter\Messaging\Exchanges;

class TestCollaborationRabbitMQExchangeListener extends TestExchangeListener
{
    protected function exchangeName()
    {
        return Exchanges::$COLLABORATION_EXCHANGE_NAME;
    }
}