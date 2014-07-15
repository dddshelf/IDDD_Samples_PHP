<?php

namespace SaasOvation\Common\Port\Adapter\Notification;

use BadMethodCallException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use SaasOvation\Common\Event\EventStore;
use SaasOvation\Common\Notification\Notification;
use SaasOvation\Common\Notification\NotificationPublisher;
use SaasOvation\Common\Notification\NotificationSerializer;
use SaasOvation\Common\Notification\PublishedNotificationTrackerStore;
use SaasOvation\Common\Port\Adapter\Messaging\RabbitMQ\ConnectionSettings;
use SaasOvation\Common\Port\Adapter\Messaging\RabbitMQ\Exchange;
use SaasOvation\Common\Port\Adapter\Messaging\RabbitMQ\MessageParameters;
use SaasOvation\Common\Port\Adapter\Messaging\RabbitMQ\MessageProducer;

class RabbitMQNotificationPublisher implements NotificationPublisher
{
    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @var string
     */
    private $exchangeName;

    /**
     * @var PublishedNotificationTrackerStore
     */
    private $publishedNotificationTrackerStore;

    public function __construct(
        EventStore $anEventStore,
        PublishedNotificationTrackerStore $aPublishedNotificationTrackerStore,
        $aMessagingLocator
    ) {
        $this->setEventStore($anEventStore);
        $this->setExchangeName($aMessagingLocator);
        $this->setPublishedNotificationTrackerStore($aPublishedNotificationTrackerStore);
    }

    public function publishNotifications()
    {
        $publishedNotificationTracker = $this->publishedNotificationTrackerStore()->publishedNotificationTracker();

        $notifications = $this->listUnpublishedNotifications($publishedNotificationTracker->mostRecentPublishedNotificationId());

        $messageProducer = $this->messageProducer();

        try {
            foreach ($notifications as $notification) {
                $this->publish($notification, $messageProducer);
            }

            $this->publishedNotificationTrackerStore()->trackMostRecentPublishedNotification(
                $publishedNotificationTracker,
                $notifications
            );
        } finally {
            $messageProducer->close();
        }
    }

    public function internalOnlyTestConfirmation() {
        throw new BadMethodCallException('Not supported by production implementation.');
    }

    private function eventStore()
    {
        return $this->eventStore;
    }

    private function setEventStore(EventStore $anEventStore)
    {
        $this->eventStore = $anEventStore;
    }

    private function exchangeName()
    {
        return $this->exchangeName;
    }

    private function setExchangeName($anExchangeName)
    {
        $this->exchangeName = $anExchangeName;
    }

    private function listUnpublishedNotifications($aMostRecentPublishedMessageId)
    {
        return $this->notificationsFrom(
            $this->eventStore()->allStoredEventsSince($aMostRecentPublishedMessageId)
        );
    }

    private function messageProducer()
    {
        // creates my exchange if non-existing
        $exchange = Exchange::fanOutInstance(
            ConnectionSettings::instance(),
            $this->exchangeName(),
            true
        );

        // create a message producer used to forward events
        return MessageProducer::instance($exchange);
    }

    private function notificationsFrom(Collection $aStoredEvents)
    {
        $notifications = new ArrayCollection();

        foreach ($aStoredEvents as $storedEvent) {
            $domainEvent = $storedEvent->toDomainEvent();

            $notification = new Notification(
                $storedEvent->eventId(),
                $domainEvent
            );

            $notifications->add($notification);
        }

        return $notifications;
    }

    private function publish(Notification $aNotification, MessageProducer $aMessageProducer)
    {
        $messageParameters = MessageParameters::durableTextParameters(
            $aNotification->typeName(),
            intval($aNotification->notificationId()),
            $aNotification->occurredOn()
        );

        $notification = NotificationSerializer::instance()->serialize($aNotification);

        $aMessageProducer->send($notification, $messageParameters);
    }

    private function publishedNotificationTrackerStore()
    {
        return $this->publishedNotificationTrackerStore;
    }

    private function setPublishedNotificationTrackerStore(PublishedNotificationTrackerStore $publishedNotificationTrackerStore)
    {
        $this->publishedNotificationTrackerStore = $publishedNotificationTrackerStore;
    }
}
