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
        $args = $this->prepareEventArgs(func_get_args());

        $stringValue = call_user_func_array([$this, 'getStringValue'], $args);

        return null === $stringValue ? null : intval($stringValue);
    }

    public function eventBooleanValue()
    {
        $args = $this->prepareEventArgs(func_get_args());

        $stringValue = call_user_func_array([$this, 'getStringValue'], $args);

        return null === $stringValue ? null : boolval($stringValue);
    }

    public function eventDateValue()
    {
        $args = $this->prepareEventArgs(func_get_args());

        $stringValue = call_user_func_array([$this, 'getStringValue'], $args);

        return null === $stringValue ? null : (new DateTimeImmutable())->setTimestamp(intval($stringValue));
    }

    public function eventDoubleValue()
    {
        $args = $this->prepareEventArgs(func_get_args());

        $stringValue = call_user_func_array([$this, 'getStringValue'], $args);

        return null === $stringValue ? null : doubleval($stringValue);
    }

    public function eventFloatValue()
    {
        $args = $this->prepareEventArgs(func_get_args());

        $stringValue = call_user_func_array([$this, 'getStringValue'], $args);

        return null === $stringValue ? null : floatval($stringValue);
    }

    public function eventIntegerValue()
    {
        $args = $this->prepareEventArgs(func_get_args());

        $stringValue = call_user_func_array([$this, 'getStringValue'], $args);

        return null === $stringValue ? null : intval($stringValue);
    }

    public function eventLongValue()
    {
        $args = $this->prepareEventArgs(func_get_args());

        $stringValue = call_user_func_array([$this, 'getStringValue'], $args);

        return null === $stringValue ? null : intval($stringValue);
    }

    public function eventStringValue()
    {
        $args = $this->prepareEventArgs(func_get_args());

        $stringValue = call_user_func_array([$this, 'getStringValue'], $args);

        return $stringValue;
    }

    public function notificationId()
    {
        $notificationId = $this->longValue('notification_id');

        return $notificationId;
    }

    public function notificationIdAsString()
    {
        $notificationId = $this->stringValue('notification_id');

        return $notificationId;
    }

    public function occurredOn()
    {
        $time = $this->longValue('occurred_on');

        return (new DateTimeImmutable())->setTimestamp($time);
    }

    public function typeName()
    {
        $typeName = $this->stringValue('type_name');

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

    private function prepareEventArgs($arguments)
    {
        $args = [
            $this->event()
        ];

        foreach ($arguments as $arg) {
            $args[] = $arg;
        }

        return $args;
    }
}
