<?php

namespace SaasOvation\Common\Test\Domain\Model;

use SaasOvation\Common\Port\Adapter\Messaging\Exchanges;

class TestAgilePMSlothMQExchangeListener extends BaseTestExchangeListener
{
    protected function exchangeName()
    {
        return Exchanges::$AGILEPM_EXCHANGE_NAME;
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