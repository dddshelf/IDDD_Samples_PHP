<?php

namespace SaasOvation\Common\Test\Notification;

use SaasOvation\Common\Test\CommonTestCase;
use SaasOvation\Common\Test\Event\MockEventStore;
use SaasOvation\Common\Port\Adapter\Notification\RabbitMQNotificationPublisher;
use SaasOvation\Common\Port\Adapter\Persistence\Doctrine\DoctrinePublishedNotificationTrackerStore;

class NotificationPublisherCreationTest extends CommonTestCase
{
    public function testNewNotificationPublisher()
    {
        $eventStore = new MockEventStore();

        $this->assertNotNull($eventStore);

        $publishedNotificationTrackerStore = new DoctrinePublishedNotificationTrackerStore(
            $this->container->get('doctrine.orm.entity_manager'),
            'unit.test'
        );

        $notificationPublisher = new RabbitMQNotificationPublisher(
            $eventStore,
            $publishedNotificationTrackerStore,
            'unit.test'
        );

        $this->assertNotNull($notificationPublisher);
    }
}
