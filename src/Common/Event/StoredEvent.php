<?php

namespace SaasOvation\Common\Event;

use DateTimeInterface;
use SaasOvation\Common\AssertionConcern;

class StoredEvent extends AssertionConcern
{
    /**
     * @var string
     */
    private $eventBody;

    /**
     * @var int
     */
    private $eventId;

    /**
     * @var DateTimeInterface
     */
    private $occurredOn;

    /**
     * @var string
     */
    private $typeName;

    public function __construct($aTypeName, DateTimeInterface $anOccurredOn, $anEventBody, $anEventId = -1)
    {
        $this->setEventBody($anEventBody);
        $this->setOccurredOn($anOccurredOn);
        $this->setTypeName($aTypeName);
        $this->setEventId($anEventId);
    }

    public function eventBody()
    {
        return $this->eventBody;
    }

    public function eventId()
    {
        return $this->eventId;
    }

    public function occurredOn()
    {
        return $this->occurredOn;
    }

    public function toDomainEvent()
    {
        $domainEventClass = $this->typeName();

        $domainEvent = EventSerializer::instance()->deserialize($this->eventBody(), $domainEventClass);

        return $domainEvent;
    }

    public function typeName()
    {
        return $this->typeName;
    }

    public function equals($anObject)
    {
        $equalObjects = false;

        if (null !== $anObject && get_class($this) == get_class($anObject)) {
            $equalObjects = $this->eventId() == $anObject->eventId();
        }

        return $equalObjects;
    }

    public function __toString()
    {
        return "StoredEvent [eventBody=" . $this->eventBody . ", eventId=" . $this->eventId . ", occurredOn=" . $this->occurredOn . ", typeName=" . $this->typeName . "]";
    }

    protected function setEventBody($anEventBody)
    {
        $this->assertArgumentNotEmpty($anEventBody, 'The event body is required.');
        $this->assertArgumentLength($anEventBody, 1, 65000, 'The event body must be 65000 characters or less.');

        $this->eventBody = $anEventBody;
    }

    public function setEventId($anEventId)
    {
        $this->eventId = $anEventId;
    }

    protected function setOccurredOn(DateTimeInterface $anOccurredOn)
    {
        $this->occurredOn = $anOccurredOn;
    }

    protected function setTypeName($aTypeName)
    {
        $this->assertArgumentNotEmpty($aTypeName, 'The event type name is required.');
        $this->assertArgumentLength($aTypeName, 1, 100, 'The event type name must be 100 characters or less.');

        $this->typeName = $aTypeName;
    }
}
