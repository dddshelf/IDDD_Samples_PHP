<?php

namespace SaasOvation\Common\Notification;

// import java->io->Serializable;
// import java->util->Date;
use DateTimeInterface;
use Serializable;

use SaasOvation\Common\AssertionConcern;
use SaasOvation\Common\Domain\Model\DomainEvent;

class Notification extends AssertionConcern implements Serializable
{
    /**
     * @var DomainEvent
     */
    private $event;

    /**
     * @var int
     */
    private $notificationId;

    /**
     * @var DateTimeInterface
     */
    private $occurredOn;

    /**
     * @var string
     */
    private $typeName;

    /**
     * @var int
     */
    private $version;

    public function __construct($aNotificationId, DomainEvent $anEvent)
    {
        $this->setEvent($anEvent);
        $this->setNotificationId($aNotificationId);
        $this->setOccurredOn($anEvent->occurredOn());
        $this->setTypeName(get_class($anEvent));
        $this->setVersion($anEvent->eventVersion());
    }

    public function event()
    {
        return $this->event;
    }

    public function notificationId()
    {
        return $this->notificationId;
    }

    public function occurredOn()
    {
        return $this->occurredOn;
    }

    public function typeName()
    {
        return $this->typeName;
    }

    public function version()
    {
        return $this->version;
    }

    public function equals($anObject)
    {
        if (null !== $anObject
            && get_class($this) == get_class($anObject)
        ) {
            return $this->notificationId() === $anObject->notificationId();
        }

        return false;
    }

    public function __toString()
    {
        return 'Notification [event=' . $this->event . ', notificationId=' . $this->notificationId
        . ', occurredOn=' . $this->occurredOn . ', typeName='
        . $this->typeName . ', version=' . $this->version . ']';
    }

    protected function setEvent(DomainEvent $anEvent)
    {
        $this->assertArgumentNotNull($anEvent, 'The event is required.');

        $this->event = $anEvent;
    }

    protected function setNotificationId($aNotificationId)
    {
        $this->notificationId = $aNotificationId;
    }

    protected function setOccurredOn(DateTimeInterface $anOccurredOn)
    {
        $this->occurredOn = $anOccurredOn;
    }

    protected function setTypeName($aTypeName)
    {
        $this->assertArgumentNotEmpty($aTypeName, 'The type name is required.');
        $this->assertArgumentLength($aTypeName, 100, 'The type name must be 100 characters or less.');

        $this->typeName = $aTypeName;
    }

    private function setVersion($aVersion)
    {
        $this->version = $aVersion;
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        return serialize([
            'event'             => $this->event(),
            'notificationId'    => $this->notificationId(),
            'ocurredOn'         => $this->occurredOn(),
            'typeName'          => $this->typeName(),
            'version'           => $this->version()
        ]);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);

        $this->setEvent($data['event']);
        $this->setNotificationId($data['notificationId']);
        $this->setOccurredOn($data['ocurredOn']);
        $this->setTypeName($data['typeName']);
        $this->setVersion($data['version']);
    }
}
