<?php

namespace SaasOvation\IdentityAccess\Test\Application;

use SaasOvation\Common\Event\EventStore;
use SaasOvation\Common\Test\Event\TestableDomainEvent;
use SaasOvation\Common\Notification\NotificationLogFactory;
use SaasOvation\Common\Notification\NotificationLogId;
use SaasOvation\Common\Notification\NotificationPublisher;
use SaasOvation\IdentityAccess\Application\ApplicationServiceRegistry;
use SaasOvation\IdentityAccess\Application\NotificationApplicationService;

class NotificationApplicationServiceTest extends ApplicationServiceTest
{
    /**
     * @var EventStore
     */
    protected $eventStore;

    /**
     * @var NotificationApplicationService
     */
    private $notificationApplicationService;

    /**
     * @var NotificationPublisher
     */
    private $notificationPublisher;
    

    public function testCurrentNotificationLog()
    {
        $log = $this->notificationApplicationService->currentNotificationLog();

        $this->assertTrue(NotificationLogFactory::notificationsPerLog() >= $log->totalNotifications());
        $this->assertTrue($this->eventStore->countStoredEvents() >= $log->totalNotifications());
        $this->assertFalse($log->hasNextNotificationLog());
        $this->assertTrue($log->hasPreviousNotificationLog());
        $this->assertFalse($log->isArchived());
    }

    public function testNotificationLog()
    {
        $id = NotificationLogId::first(NotificationLogFactory::notificationsPerLog());

        $log = $this->notificationApplicationService->notificationLog($id->getEncoded());

        $this->assertEquals(NotificationLogFactory::notificationsPerLog(), $log->totalNotifications());
        $this->assertTrue($this->eventStore->countStoredEvents() >= $log->totalNotifications());
        $this->assertTrue($log->hasNextNotificationLog());
        $this->assertFalse($log->hasPreviousNotificationLog());
        $this->assertTrue($log->isArchived());
    }

    public function testPublishNotifications()
    {
        $this->notificationApplicationService->publishNotifications();

        $this->assertTrue($this->notificationPublisher->internalOnlyTestConfirmation());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->notificationApplicationService = ApplicationServiceRegistry::notificationApplicationService();

        $this->eventStore = $this->notificationApplicationService->eventStore();

        $this->notificationPublisher = $this->notificationApplicationService->notificationPublisher();

        for ($idx = 1; $idx <= 31; ++$idx) {
            $this->eventStore->append(new TestableDomainEvent($idx, 'Event: ' . $idx));
        }
    }
}
