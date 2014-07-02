<?php

namespace SaasOvation\Common\Test\Domain\Model;

use SaasOvation\Common\Port\Adapter\Messaging\Exchanges;

class TestIdentityAccessRabbitMQExchangeListener extends TestExchangeListener
{
    protected function exchangeName()
    {
        return Exchanges::$IDENTITY_ACCESS_EXCHANGE_NAME;
    }
}