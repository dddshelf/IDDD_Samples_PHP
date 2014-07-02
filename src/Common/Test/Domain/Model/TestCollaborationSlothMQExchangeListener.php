<?php

namespace SaasOvation\Common\Test\Domain\Model;

use SaasOvation\Common\Port\Adapter\Messaging\Exchanges;

class TestCollaborationSlothMQExchangeListener extends BaseTestExchangeListener
{
    protected function exchangeName()
    {
        return Exchanges::$COLLABORATION_EXCHANGE_NAME;
    }

    protected function listensTo()
    {
        return []; // receive all
    }

    protected function name()
    {
        return __CLASS__;
    }
}
