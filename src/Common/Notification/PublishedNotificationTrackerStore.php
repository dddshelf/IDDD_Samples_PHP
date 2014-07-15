<?php

namespace SaasOvation\Common\Notification;

use Doctrine\Common\Collections\Collection;

interface PublishedNotificationTrackerStore
{
    public function publishedNotificationTracker();

    public function trackMostRecentPublishedNotification(PublishedNotificationTracker $aPublishedNotificationTracker, Collection $aNotifications);

    public function typeName();
}
