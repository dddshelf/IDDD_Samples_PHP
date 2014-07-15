<?php

namespace SaasOvation\Common\Test\Notification;

use SaasOvation\Common\Notification\NotificationLogFactory;
use SaasOvation\Common\Notification\NotificationLogId;
use SaasOvation\Common\Test\CommonTestCase;
use SaasOvation\Common\Test\Event\MockEventStore;

class NotificationLogTest extends CommonTestCase
{
    public function testCurrentNotificationLogFromFactory()
    {
        $eventStore = $this->eventStore();
        $factory = new NotificationLogFactory($eventStore);
        $log = $factory->createCurrentNotificationLog();

        $this->assertTrue(NotificationLogFactory::notificationsPerLog() >= $log->totalNotifications());
        $this->assertTrue($eventStore->countStoredEvents() >= $log->totalNotifications());
        $this->assertFalse($log->hasNextNotificationLog());
        $this->assertTrue($log->hasPreviousNotificationLog());
        $this->assertFalse($log->isArchived());
    }

    public function testFirstNotificationLogFromFactory()
    {
        $eventStore = $this->eventStore();
        $id = NotificationLogId::first(NotificationLogFactory::notificationsPerLog());
        $factory = new NotificationLogFactory($eventStore);
        $log = $factory->createNotificationLog($id);

        $this->assertEquals(NotificationLogFactory::notificationsPerLog(), $log->totalNotifications());
        $this->assertTrue($eventStore->countStoredEvents() >= $log->totalNotifications());
        $this->assertTrue($log->hasNextNotificationLog());
        $this->assertFalse($log->hasPreviousNotificationLog());
        $this->assertTrue($log->isArchived());
    }

    public function testPreviousOfCurrentNotificationLogFromFactory()
    {
        $eventStore = $this->eventStore();
        $totalEvents = $eventStore->countStoredEvents();
        $shouldBePrevious = $totalEvents > (NotificationLogFactory::notificationsPerLog() * 2);
        $factory = new NotificationLogFactory($eventStore);
        $log = $factory->createCurrentNotificationLog();

        $previousId = $log->decodedPreviousNotificationLogId();
        $log = $factory->createNotificationLog($previousId);

        $this->assertEquals(NotificationLogFactory::notificationsPerLog(), $log->totalNotifications());
        $this->assertTrue($totalEvents >= $log->totalNotifications());
        $this->assertTrue($log->hasNextNotificationLog());
        $this->assertEquals($shouldBePrevious, $log->hasPreviousNotificationLog());
        $this->assertTrue($log->isArchived());
    }

    public function testEncodedWithDecodedNavigationIds()
    {
        $eventStore = $this->eventStore();
        $factory = new NotificationLogFactory($eventStore);
        $log = $factory->createCurrentNotificationLog();

        $currentId = $log->notificationLogId();
        $decodedCurrentLogId = $log->decodedNotificationLogId();
        $this->assertEquals($log->decodedNotificationLogId(), NotificationLogId::createFromNotificationLogId($currentId));

        $previousId = $log->previousNotificationLogId();
        $decodedPreviousLogId = $log->decodedPreviousNotificationLogId();
        $this->assertEquals($decodedPreviousLogId, NotificationLogId::createFromNotificationLogId($previousId));
        $log = $factory->createNotificationLog($log->decodedPreviousNotificationLogId());

        $nextId = $log->nextNotificationLogId();
        $decodedNextLogId = $log->decodedNextNotificationLogId();
        $this->assertEquals($decodedNextLogId, NotificationLogId::createFromNotificationLogId($nextId));
        $this->assertEquals($decodedCurrentLogId, $decodedNextLogId);
    }

    private function eventStore()
    {
        $eventStore = new MockEventStore();

        $this->assertNotNull($eventStore);

        return $eventStore;
    }
}
