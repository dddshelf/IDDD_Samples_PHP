<?php

namespace SaasOvation\Common\Port\Adapter\Persistence\Doctrine;

use Doctrine\ORM\EntityManager;
use Exception;
use Doctrine\Common\Collections\Collection;
use SaasOvation\Common\Notification\PublishedNotificationTracker;
use SaasOvation\Common\Notification\PublishedNotificationTrackerStore;

class DoctrinePublishedNotificationTrackerStore extends AbstractDoctrineEntityManager implements PublishedNotificationTrackerStore
{
    /**
     * @var string
     */
    private $typeName;

    public function __construct(EntityManager $anEntityManager, $aPublishedNotificationTrackerType)
    {
        parent::__construct($anEntityManager);
        $this->setTypeName($aPublishedNotificationTrackerType);
    }

    public function publishedNotificationTracker()
    {
        $aTypeName = $this->typeName();

        $query = $this->entityManager()->createQuery(
            'SELECT pnt
             FROM SaasOvation\Common\Notification\PublishedNotificationTracker pnt
             WHERE pnt.typeName = ?1'
        );

        $query->setParameter(1, $aTypeName);

        $publishedNotificationTracker = null;

        try {
            $publishedNotificationTracker = $query->getSingleResult();
        } catch (Exception $e) {
            // fall through
        }

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

            $this->entityManager()->persist($aPublishedNotificationTracker);
            $this->entityManager()->flush($aPublishedNotificationTracker);
        }
    }

    public function typeName()
    {
        return $this->typeName;
    }

    private function setTypeName($aTypeName)
    {
        $this->typeName = $aTypeName;
    }
}
