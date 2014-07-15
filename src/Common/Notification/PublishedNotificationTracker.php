<?php

namespace SaasOvation\Common\Notification;

use SaasOvation\Common\AssertionConcern;

class PublishedNotificationTracker extends AssertionConcern
{
    /**
     * @var int
     */
    private $concurrencyVersion;

    /**
     * @var int
     */
    private $mostRecentPublishedNotificationId;

    /**
     * @var int
     */
    private $publishedNotificationTrackerId;

    /**
     * @var string
     */
    private $typeName;
    
    public function __construct($aTypeName)
    {
        $this->setTypeName($aTypeName);
    }

    public function failWhenConcurrencyViolation($aVersion)
    {
        $this->assertStateTrue(
            $aVersion === $this->concurrencyVersion(),
            'Concurrency Violation: Stale data detected. Entity was already modified.'
        );
    }

    public function mostRecentPublishedNotificationId()
    {
        return $this->mostRecentPublishedNotificationId;
    }

    public function setMostRecentPublishedNotificationId($aMostRecentPublishedNotificationId)
    {
        $this->mostRecentPublishedNotificationId = $aMostRecentPublishedNotificationId;
    }

    public function publishedNotificationTrackerId()
    {
        return $this->publishedNotificationTrackerId;
    }

    public function typeName()
    {
        return $this->typeName;
    }

    public function equals($anObject)
    {
        $equalObjects = false;

        if (null !== $anObject && get_class($this) === get_class($anObject)) {
            $equalObjects = $this->publishedNotificationTrackerId() == $anObject->publishedNotificationTrackerId()
                && $this->typeName() == $anObject->typeName()
                && $this->mostRecentPublishedNotificationId() == $anObject->mostRecentPublishedNotificationId();
        }

        return $equalObjects;
    }

    public function __toString()
    {
        return 'PublishedNotificationTracker [mostRecentPublishedNotificationId=' . $this->mostRecentPublishedNotificationId
        . ', publishedNotificationTrackerId=' . $this->publishedNotificationTrackerId . ', typeName=' . $this->typeName . ']';
    }

    protected function concurrencyVersion()
    {
        return $this->concurrencyVersion;
    }

    protected function setConcurrencyVersion($aConcurrencyVersion)
    {
        $this->concurrencyVersion = $aConcurrencyVersion;
    }

    protected function setPublishedNotificationTrackerId($aPublishedNotificationTrackerId)
    {
        $this->publishedNotificationTrackerId = $aPublishedNotificationTrackerId;
    }

    protected function setTypeName($aTypeName)
    {
        $this->assertArgumentNotEmpty($aTypeName, 'The tracker type name is required.');
        $this->assertArgumentLength($aTypeName, 100, 'The tracker type name must be 100 characters or less.');

        $this->typeName = $aTypeName;
    }
}
