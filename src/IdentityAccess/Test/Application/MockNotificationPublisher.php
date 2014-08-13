<?php

namespace SaasOvation\IdentityAccess\Test\Application;

use SaasOvation\Common\Notification\NotificationPublisher;

class MockNotificationPublisher implements NotificationPublisher
{
    /**
     * @var boolean
     */
    private $confirmed;

    public function publishNotifications()
    {
        $this->confirmed = true;
    }

    public function internalOnlyTestConfirmation()
    {
        return $this->confirmed;
    }
}
