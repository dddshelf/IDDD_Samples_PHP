<?php

namespace SaasOvation\Common\Notification;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class NotificationLog
{
    /**
     * @var boolean
     */
    private $archived;

    /**
     * @var Collection
     */
    private $notifications;

    /**
     * @var string
     */
    private $notificationLogId;

    /**
     * @var string
     */
    private $nextNotificationLogId;

    /**
     * @var string
     */
    private $previousNotificationLogId;
    
    public function __construct(
        $aNotificationLogId,
        $aNextNotificationLogId,
        $aPreviousNotificationLogId,
        Collection $aNotifications,
        $anArchivedIndicator
    ) {
        $this->setArchived($anArchivedIndicator);
        $this->setNextNotificationLogId($aNextNotificationLogId);
        $this->setNotificationLogId($aNotificationLogId);
        $this->setNotifications($aNotifications);
        $this->setPreviousNotificationLogId($aPreviousNotificationLogId);
    }

    public function isArchived()
    {
        return $this->archived;
    }

    public function notifications()
    {
        return $this->notifications;
    }

    public function decodedNotificationLogId()
    {
        return NotificationLogId::createFromNotificationLogId($this->notificationLogId());
    }

    public function notificationLogId()
    {
        return $this->notificationLogId;
    }

    public function decodedNextNotificationLogId()
    {
        return NotificationLogId::createFromNotificationLogId($this->nextNotificationLogId());
    }

    public function nextNotificationLogId()
    {
        return $this->nextNotificationLogId;
    }

    public function hasNextNotificationLog()
    {
        return null !== $this->nextNotificationLogId();
    }

    public function decodedPreviousNotificationLogId()
    {
        return NotificationLogId::createFromNotificationLogId($this->previousNotificationLogId());
    }

    public function previousNotificationLogId()
    {
        return $this->previousNotificationLogId;
    }

    public function hasPreviousNotificationLog()
    {
        return null !== $this->previousNotificationLogId();
    }

    public function totalNotifications()
    {
        return $this->notifications->count();
    }

    private function setNotifications(Collection $aNotifications)
    {
        $this->notifications = $aNotifications;
    }

    private function setNotificationLogId($aNotificationLogId)
    {
        $this->notificationLogId = $aNotificationLogId;
    }

    private function setNextNotificationLogId($aNextNotificationLogId)
    {
        $this->nextNotificationLogId = $aNextNotificationLogId;
    }

    private function setPreviousNotificationLogId($aPreviousNotificationLogId)
    {
        $this->previousNotificationLogId = $aPreviousNotificationLogId;
    }

    private function setArchived($aArchived)
    {
        $this->archived = $aArchived;
    }
}
