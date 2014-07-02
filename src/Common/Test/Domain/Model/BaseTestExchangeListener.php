<?php

namespace SaasOvation\Common\Test\Domain\Model;

use SaasOvation\Common\Notification\NotificationReader;
use SaasOvation\Common\Port\Adapter\Messaging\RabbitMQ\ExchangeListener;

abstract class BaseTestExchangeListener extends ExchangeListener
{
    /**
     * @var EventTrackingTestCase
     */
    protected $eventTrackingTestCase;

    public function __construct(EventTrackingTestCase $anEventTrackingTestCase)
    {
        $this->eventTrackingTestCase = $anEventTrackingTestCase;

        parent::__construct();
    }

    public function filteredDispatch($aType, $aTextMessage)
    {
        $notification = NotificationReader::fromString($aTextMessage);
        $this->eventTrackingTestCase->handledNotifications()->set($notification->notificationIdAsString(), $aType);
    }
}