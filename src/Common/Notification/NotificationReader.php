<?php

namespace SaasOvation\Common\Notification;

use DateTimeImmutable;
use SaasOvation\Common\Media\AbstractJSONMediaReader;

class NotificationReader extends AbstractJSONMediaReader
{
    /**
     * @var stdClass
     */
    private $event;

    public static function fromString($aJSONRepresentation)
    {
        $instance =  parent::fromString($aJSONRepresentation);

        $instance->setEvent(
            $instance->representation()->event
        );

        return $instance;
    }

    public function eventBigDecimalValue()
    {
        $stringValue = $this->stringValue($this->event(), func_get_args());

        return null === $stringValue ? null : intval($stringValue);
    }

    public function eventBooleanValue()
    {
        $stringValue = $this->stringValue($this->event(), func_get_args());

        return null === $stringValue ? null : boolval($stringValue);
    }

    public function eventDateValue()
    {
        $stringValue = $this->stringValue($this->event(), func_get_args());

        return null === $stringValue ? null : new DateTimeImmutable(intval($stringValue));
    }

    public function eventDoubleValue()
    {
        $stringValue = $this->stringValue($this->event(), func_get_args());

        return null === $stringValue ? null : doubleval($stringValue);
    }

    public function eventFloatValue()
    {
        $stringValue = $this->stringValue($this->event(), func_get_args());

        return null === $stringValue ? null : floatval($stringValue);
    }

    public function eventIntegerValue()
    {
        $stringValue = $this->stringValue($this->event(), func_get_args());

        return null === $stringValue ? null : intval($stringValue);
    }

    public function eventLongValue()
    {
        $stringValue = $this->stringValue($this->event(), func_get_args());

        return null === $stringValue ? null : intval($stringValue);
    }

    public function eventStringValue()
    {
        $stringValue = $this->stringValue($this->event(), func_get_args());

        return $stringValue;
    }

    public function notificationId()
    {
        $notificationId = $this->longValue('notificationId');

        return $notificationId;
    }

    public function notificationIdAsString()
    {
        $notificationId = $this->stringValue('notification_id');

        return $notificationId;
    }

    public function occurredOn()
    {
        $time = $this->longValue('occurredOn');

        return new DateTimeImmutable($time);
    }

    public function typeName()
    {
        $typeName = $this->stringValue('typeName');

        return $typeName;
    }

    public function version()
    {
        $version = $this->integerValue('version');

        return $version;
    }

    private function event()
    {
        return $this->event;
    }

    private function setEvent($anEvent)
    {
        $this->event = $anEvent;
    }
}
