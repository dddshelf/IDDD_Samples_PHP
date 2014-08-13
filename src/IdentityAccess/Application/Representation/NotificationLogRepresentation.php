<?php

namespace SaasOvation\IdentityAccess\Application\Representation;

use Doctrine\Common\Collections\Collection;
use SaasOvation\Common\Media\Link;
use SaasOvation\Common\Notification\Notification;
use SaasOvation\Common\Notification\NotificationLog;

class NotificationLogRepresentation
{
    /**
     * @var boolean
     */
    private $archived;

    /**
     * @var string
     */
    private $id;

    /**
     * @var Collection
     */
    private $notifications;

    /**
     * @var Link
     */
    private $linkNext;
    
    /**
     * @var Link
     */
    private $linkPrevious;

    /**
     * @var Link
     */
    private $linkSelf;

    public function __construct(NotificationLog $aLog)
    {
        $this->initializeFrom($aLog);
    }

    public function getArchived()
    {
        return $this->archived;
    }

    public function getId()
    {
        return $this->id;
    }

    public function addNotification(Notification $aNotification)
    {
        $this->getNotifications()->add($aNotification);
    }

    public function getNotifications()
    {
        return $this->notifications;
    }

    public function getNotificationsCount()
    {
        return $this->getNotifications()->count();
    }

    public function hasNotifications()
    {
        return $this->getNotificationsCount() > 0;
    }

    public function getLinkNext()
    {
        return $this->linkNext;
    }

    public function setLinkNext(Link $aNext)
    {
        $this->linkNext = $aNext;
    }

    public function getLinkPrevious()
    {
        return $this->linkPrevious;
    }

    public function setLinkPrevious(Link $aPrevious)
    {
        $this->linkPrevious = $aPrevious;
    }

    public function getLinkSelf()
    {
        return $this->linkSelf;
    }

    public function setLinkSelf(Link $aSelf)
    {
        $this->linkSelf = $aSelf;
    }

    private function initializeFrom(NotificationLog $aLog)
    {
        $this->setArchived($aLog->isArchived());
        $this->setId($aLog->notificationLogId());
        $this->setNotifications($aLog->notifications());
    }

    private function setArchived($isArchived)
    {
        $this->archived = $isArchived;
    }

    private function setId($anId)
    {
        $this->id = $anId;
    }

    private function setNotifications(Collection $aNotifications)
    {
        $this->notifications = $aNotifications;
    }
}
