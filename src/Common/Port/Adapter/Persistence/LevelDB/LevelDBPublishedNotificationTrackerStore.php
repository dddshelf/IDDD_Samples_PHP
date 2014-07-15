<?php

namespace SaasOvation\Common\Port\Adapter\Persistence\LevelDB;

use Doctrine\Common\Collections\Collection;
use SaasOvation\Common\Notification\Notification;
use SaasOvation\Common\Notification\PublishedNotificationTracker;
use SaasOvation\Common\Notification\PublishedNotificationTrackerStore;

class LevelDBPublishedNotificationTrackerStore
    extends AbstractLevelDBRepository
    implements PublishedNotificationTrackerStore
{
    private static $PRIMARY = 'PUBNOTIF_TRACKER#PK';

    /**
     * @var string
     */
    private $typeName;

    public function __construct($aLevelDBDirectoryPath, $aPublishedNotificationTrackerType)
    {
        parent::__construct($aLevelDBDirectoryPath);

        $this->setTypeName($aPublishedNotificationTrackerType);
    }

    public function publishedNotificationTracker()
    {
        return $this->publishedNotificationTrackerFromTypeName();
    }

    public function publishedNotificationTrackerFromTypeName()
    {
        $uow = LevelDBUnitOfWork::readOnly($this->database());

        $primaryKey = LevelDBKey::createFromCategoryAndSegments(static::$PRIMARY, $this->typeName());

        $publishedNotificationTracker = $uow->readObject($primaryKey, PublishedNotificationTracker::class);

        if (null === $publishedNotificationTracker) {
            $publishedNotificationTracker = new PublishedNotificationTracker($this->typeName());
        }

        return $publishedNotificationTracker;
    }

    public function trackMostRecentPublishedNotification(PublishedNotificationTracker $aPublishedNotificationTracker, Collection $aNotifications)
    {
        $lastIndex = $aNotifications->count() - 1;

        if ($lastIndex >= 0) {
            $mostRecentId = $aNotifications->get($lastIndex)->notificationId();

            $aPublishedNotificationTracker->setMostRecentPublishedNotificationId($mostRecentId);

            $uow = LevelDBUnitOfWork::start($this->database());

            $this->save($aPublishedNotificationTracker, $uow);
        }
    }

    public function typeName()
    {
        return $this->typeName;
    }

    private function save(PublishedNotificationTracker $aPublishedNotificationTracker, LevelDBUnitOfWork $aUoW)
    {
        $primaryKey = LevelDBKey::createFromCategoryAndSegments(static::$PRIMARY, $this->typeName());

        $aUoW->write($primaryKey, $aPublishedNotificationTracker);
    }

    private function setTypeName($aTypeName)
    {
        $this->typeName = $aTypeName;
    }
}
