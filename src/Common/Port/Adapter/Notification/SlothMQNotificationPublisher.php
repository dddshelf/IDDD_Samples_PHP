<?php

namespace SaasOvation\Common\Port\Adapter\Notification;

use BadMethodCallException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Exception;
use SaasOvation\Common\Domain\Model\DomainEvent;
use SaasOvation\Common\Event\EventStore;
use SaasOvation\Common\Event\StoredEvent;
use SaasOvation\Common\Notification\Notification;
use SaasOvation\Common\Notification\NotificationPublisher;
use SaasOvation\Common\Notification\NotificationSerializer;
use SaasOvation\Common\Notification\PublishedNotificationTracker;
use SaasOvation\Common\Notification\PublishedNotificationTrackerStore;
use SaasOvation\Common\Port\Adapter\Messaging\SlothMQ\ExchangePublisher;

class SlothMQNotificationPublisher implements NotificationPublisher
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
     * @var ExchangePublisher
     */
    private $exchangePublisher;

    /**
     * @var PublishedNotificationTrackerStore
     */
    private $publishedNotificationTrackerStore;

    public function __construct(EventStore $anEventStore, PublishedNotificationTrackerStore $aPublishedNotificationTrackerStore, $aMessagingLocator)
    {
        $this->setEventStore($anEventStore);
        $this->setExchangeName($aMessagingLocator);
        $this->setExchangePublisher(new ExchangePublisher($this->exchangeName()));
        $this->setPublishedNotificationTrackerStore($aPublishedNotificationTrackerStore);
    }

    public function publishNotifications()
    {
        $publishedNotificationTracker = $this->publishedNotificationTrackerStore()->publishedNotificationTracker();

        $notifications = $this->listUnpublishedNotifications(
            $publishedNotificationTracker->mostRecentPublishedNotificationId()
        );

        try {
            foreach ($notifications as $notification) {
                $this->publish($notification);
            }

            $this->publishedNotificationTrackerStore()->trackMostRecentPublishedNotification(
                $publishedNotificationTracker,
                $notifications
            );
        } catch (Exception $e) {
            echo 'SLOTH: NotificationPublisher problem: ' . $e->getMessage();
        }
    }

    public function internalOnlyTestConfirmation()
    {
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

    private function exchangePublisher()
    {
        return $this->exchangePublisher;
    }

    private function setExchangePublisher(ExchangePublisher $anExchangePublisher)
    {
        $this->exchangePublisher = $anExchangePublisher;
    }

    private function listUnpublishedNotifications($aMostRecentPublishedMessageId)
    {
        return $this->notificationsFrom(
            $this->eventStore()->allStoredEventsSince($aMostRecentPublishedMessageId)
        );
    }

    private function notificationsFrom(Collection $aStoredEvents)
    {
        $notifications = new ArrayCollection();

        foreach ($aStoredEvents as $storedEvent) {
            $domainEvent = $storedEvent->toDomainEvent();

            $notification = new Notification($storedEvent->eventId(), $domainEvent);

            $notifications->add($notification);
        }

        return $notifications;
    }

    private function publish(Notification $aNotification)
    {
        $notification = NotificationSerializer::instance()->serialize($aNotification);

        $this->exchangePublisher()->publish($aNotification->typeName(), $notification);
    }

    private function publishedNotificationTrackerStore()
    {
        return $this->publishedNotificationTrackerStore;
    }

    private function setPublishedNotificationTrackerStore(PublishedNotificationTrackerStore $publishedNotificationTrackerStore)
    {
        $this->$publishedNotificationTrackerStore = $publishedNotificationTrackerStore;
    }
}
