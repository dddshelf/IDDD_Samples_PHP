<?php

namespace SaasOvation\Common\Test\Domain\Model;

use SaasOvation\Common\Port\Adapter\Messaging\Exchanges;

class TestAgilePMRabbitMQExchangeListener extends TestExchangeListener
{
    protected function exchangeName()
    {
        return Exchanges::$AGILEPM_EXCHANGE_NAME;
    }
}