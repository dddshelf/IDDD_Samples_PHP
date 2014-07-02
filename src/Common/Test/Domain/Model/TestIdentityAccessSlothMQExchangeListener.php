<?php

namespace SaasOvation\Common\Test\Domain\Model;

use SaasOvation\Common\Port\Adapter\Messaging\Exchanges;

class TestIdentityAccessSlothMQExchangeListener extends BaseTestExchangeListener
{
    protected function exchangeName()
    {
        return Exchanges::$IDENTITY_ACCESS_EXCHANGE_NAME;
    }

    protected function listensTo()
    {
        return null; // receive all
    }

    protected function name()
    {
        return __CLASS__;
    }
}
