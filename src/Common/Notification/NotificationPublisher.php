<?php

namespace SaasOvation\Common\Notification;

interface NotificationPublisher
{
    public function publishNotifications();

    public function internalOnlyTestConfirmation();
}
