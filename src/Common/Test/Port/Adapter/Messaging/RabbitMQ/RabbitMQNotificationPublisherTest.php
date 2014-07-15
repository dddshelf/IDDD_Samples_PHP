<?php

namespace SaasOvation\Common\Test\Port\Adapter\Messaging\RabbitMQ;

use DateTime;
use SaasOvation\Common\Test\CommonTestCase;
use SaasOvation\Common\Domain\Model\DomainEventPublisher;
use SaasOvation\Common\Test\Event\TestableDomainEvent;
use SaasOvation\Common\Port\Adapter\Notification\RabbitMQNotificationPublisher;
use SaasOvation\Common\Port\Adapter\Persistence\Doctrine\DoctrineEventStore;
use SaasOvation\Common\Port\Adapter\Persistence\Doctrine\DoctrinePublishedNotificationTrackerStore;

class RabbitMQNotificationPublisherTest extends CommonTestCase
{
    public function testPublishNotifications()
    {
        $eventStore = $this->eventStore();

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

        $notificationPublisher->publishNotifications();
    }

    protected function setUp()
    {
        DomainEventPublisher::instance()->reset();

        parent::setUp();

        // always start with at least 20 events

        $eventStore = $this->eventStore();

        $startingDomainEventId = (new DateTime())->getTimestamp();

        for ($idx = 0; $idx < 20; ++$idx) {
            $domainEventId = $startingDomainEventId + 1;

            $event = new TestableDomainEvent($domainEventId, 'name' . $domainEventId);

            $eventStore->append($event);
        }
    }

    private function eventStore()
    {
        $eventStore = new DoctrineEventStore($this->container->get('doctrine.orm.entity_manager'));

        $this->assertNotNull($eventStore);

        return $eventStore;
    }
}
