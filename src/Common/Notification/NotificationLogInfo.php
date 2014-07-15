<?php

namespace SaasOvation\Common\Notification;

class NotificationLogInfo
{
    /**
     * @var NotificationLogId
     */
    private $notificationLogId;

    /**
     * @var int
     */
    private $totalLogged;

    public function __construct(NotificationLogId $aNotificationLogId, $aTotalLogged)
    {
        $this->notificationLogId = $aNotificationLogId;
        $this->totalLogged = $aTotalLogged;
    }

    public function notificationLogId()
    {
        return $this->notificationLogId;
    }

    public function totalLogged()
    {
        return $this->totalLogged;
    }
}
