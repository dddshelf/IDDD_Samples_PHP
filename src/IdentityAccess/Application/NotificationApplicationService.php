<?php

namespace SaasOvation\IdentityAccess\Application;

use SaasOvation\Common\Event\EventStore;
use SaasOvation\Common\Notification\NotificationLogFactory;
use SaasOvation\Common\Notification\NotificationLogId;
use SaasOvation\Common\Notification\NotificationPublisher;

class NotificationApplicationService
{
    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @var NotificationPublisher
     */
    private $notificationPublisher;

    public function __construct(EventStore $eventStore, NotificationPublisher $notificationPublisher)
    {
        $this->eventStore = $eventStore;
        $this->notificationPublisher = $notificationPublisher;
    }

    /**
     * @Transactional(readOnly=true)
     */
    public function currentNotificationLog()
    {
        $factory = new NotificationLogFactory($this->eventStore());

        return $factory->createCurrentNotificationLog();
    }

    /**
     * @Transactional(readOnly=true)
     */
    public function notificationLog($aNotificationLogId)
    {
        $factory = new NotificationLogFactory($this->eventStore());

        return $factory->createNotificationLog(NotificationLogId::createFromNotificationLogId($aNotificationLogId));
    }

    /**
     * @Transactional
     */
    public function publishNotifications()
    {
        $this->notificationPublisher()->publishNotifications();
    }

    public function eventStore()
    {
        return $this->eventStore;
    }

    public function notificationPublisher()
    {
        return $this->notificationPublisher;
    }
}
