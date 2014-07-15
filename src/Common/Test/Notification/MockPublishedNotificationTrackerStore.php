<?php

namespace SaasOvation\Common\Test\Notification;

use Doctrine\Common\Collections\Collection;
use SaasOvation\Common\Notification\PublishedNotificationTracker;
use SaasOvation\Common\Notification\PublishedNotificationTrackerStore;

class MockPublishedNotificationTrackerStore implements PublishedNotificationTrackerStore
{
    public function publishedNotificationTracker()
    {
        return new PublishedNotificationTracker('mock');
    }

    public function trackMostRecentPublishedNotification(
        PublishedNotificationTracker $aPublishedNotificationTracker,
        Collection $aNotifications
    ) {
        // no-op
    }

    public function typeName()
    {
        return 'mock';
    }
}
