<?php

namespace SaasOvation\Common\Notification;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use SaasOvation\Common\Event\EventStore;

class NotificationLogFactory
{
    // $this could be a configuration
    private static $NOTIFICATIONS_PER_LOG = 20;

    /**
     * @var EventStore
     */
    private $eventStore;

    public static function notificationsPerLog()
    {
        return self::$NOTIFICATIONS_PER_LOG;
    }

    public function __construct(EventStore $anEventStore)
    {
        $this->setEventStore($anEventStore);
    }

    public function createCurrentNotificationLog()
    {
        return $this->doCreateNotificationLog(
            $this->calculateCurrentNotificationLogId($this->eventStore)
        );
    }

    public function createNotificationLog(NotificationLogId $aNotificationLogId)
    {
        $count = $this->eventStore()->countStoredEvents();

        $info = new NotificationLogInfo($aNotificationLogId, $count);

        return $this->doCreateNotificationLog($info);
    }

    private function calculateCurrentNotificationLogId(EventStore $anEventStore)
    {
        $count = $anEventStore->countStoredEvents();

        $remainder = $count % self::$NOTIFICATIONS_PER_LOG;

        if ($remainder === 0) {
            $remainder = self::$NOTIFICATIONS_PER_LOG;
        }

        $low = $count - $remainder + 1;

        // ensures a minted id value even though there may
        // not be a full set of notifications at present
        $high = $low + self::$NOTIFICATIONS_PER_LOG - 1;

        return new NotificationLogInfo(NotificationLogId::createFromBounds($low, $high), $count);
    }

    private function doCreateNotificationLog(NotificationLogInfo $aNotificationLogInfo)
    {
        $storedEvents = $this->eventStore()->allStoredEventsBetween(
            $aNotificationLogInfo->notificationLogId()->low(),
            $aNotificationLogInfo->notificationLogId()->high()
        );

        $archivedIndicator = $aNotificationLogInfo->notificationLogId()->high() < $aNotificationLogInfo->totalLogged();

        $next = $archivedIndicator
            ? $aNotificationLogInfo->notificationLogId()->next(self::$NOTIFICATIONS_PER_LOG)
            : null
        ;

        $previous = $aNotificationLogInfo->notificationLogId()->previous(self::$NOTIFICATIONS_PER_LOG);

        $notificationLog = new NotificationLog(
            $aNotificationLogInfo->notificationLogId()->getEncoded(),
            NotificationLogId::encoded($next),
            NotificationLogId::encoded($previous),
            $this->notificationsFrom($storedEvents),
            $archivedIndicator
        );

        return $notificationLog;
    }

    private function notificationsFrom(Collection $aStoredEvents)
    {
        $notifications = new ArrayCollection();

        foreach ($aStoredEvents as $storedEvent) {
            $notifications->add(
                new Notification(
                    $storedEvent->eventId(),
                    $storedEvent->toDomainEvent()
                )
            );
        }

        return $notifications;
    }

    private function eventStore()
    {
        return $this->eventStore;
    }

    private function setEventStore(EventStore $anEventStore)
    {
        $this->eventStore = $anEventStore;
    }
}
