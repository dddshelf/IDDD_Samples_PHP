<?php

namespace SaasOvation\Common\Test\Domain\Model;

abstract class TestExchangeListener extends BaseTestExchangeListener
{
    public function clear()
    {
        $this->eventTrackingTestCase->handledEvents()->clear();
        $this->eventTrackingTestCase->handledNotifications()->clear();
    }

    protected function listensTo()
    {
        return null; // receive all
    }
}