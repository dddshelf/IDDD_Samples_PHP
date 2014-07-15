<?php

namespace SaasOvation\Common\Test\Notification;

use Exception;
use PHPUnit_Framework_TestCase;
use SaasOvation\Common\Notification\Notification;
use SaasOvation\Common\Notification\NotificationReader;
use SaasOvation\Common\Notification\NotificationSerializer;
use SaasOvation\Common\Test\Event\TestableDomainEvent;
use SaasOvation\Common\Test\Event\TestableNavigableDomainEvent;

class NotificationReaderTest extends PHPUnit_Framework_TestCase
{
    public function testReadBasicProperties()
    {
        $domainEvent = new TestableDomainEvent(100, 'testing');

        $notification = new Notification(1, $domainEvent);

        $serializer = NotificationSerializer::instance();

        $serializedNotification = $serializer->serialize($notification);

        $reader = NotificationReader::fromString($serializedNotification);

        $this->assertEquals(1, $reader->notificationId());
        $this->assertEquals('1', $reader->notificationIdAsString());
        $this->assertEquals($domainEvent->occurredOn(), $reader->occurredOn());
        $this->assertEquals($notification->typeName(), $reader->typeName());
        $this->assertEquals($notification->version(), $reader->version());
        $this->assertEquals($domainEvent->eventVersion(), $reader->version());
    }

    public function testReadDomainEventProperties()
    {
        $domainEvent = new TestableDomainEvent(100, 'testing');

        $notification = new Notification(1, $domainEvent);

        $serializer = NotificationSerializer::instance();

        $serializedNotification = $serializer->serialize($notification);

        $reader = NotificationReader::fromString($serializedNotification);

        $this->assertEquals($domainEvent->eventVersion(), $reader->eventStringValue('event_version'));
        $this->assertEquals($domainEvent->eventVersion(), $reader->eventStringValue('/event_version'));
        $this->assertEquals($domainEvent->id(), $reader->eventStringValue('id'));
        $this->assertEquals($domainEvent->id(), $reader->eventStringValue('/id'));
        $this->assertEquals($domainEvent->name(), $reader->eventStringValue("name"));
        $this->assertEquals($domainEvent->name(), $reader->eventStringValue("/name"));
        $this->assertEquals($domainEvent->occurredOn()->getTimestamp(), $reader->eventStringValue("occurred_on"));
        $this->assertEquals($domainEvent->occurredOn()->getTimestamp(), $reader->eventStringValue("/occurred_on"));
    }

    public function testReadNestedDomainEventProperties()
    {
        $domainEvent = new TestableNavigableDomainEvent(100, 'testing');

        $notification = new Notification(1, $domainEvent);

        $serializer = NotificationSerializer::instance();

        $serializedNotification = $serializer->serialize($notification);

        $reader = NotificationReader::fromString($serializedNotification);

        $this->assertEquals($domainEvent->eventVersion(), $reader->eventStringValue('event_version'));
        $this->assertEquals($domainEvent->eventVersion(), $reader->eventStringValue('/event_version'));
        $this->assertEquals($domainEvent->eventVersion(), $reader->eventIntegerValue('event_version'));
        $this->assertEquals($domainEvent->eventVersion(), $reader->eventIntegerValue('/event_version'));
        $this->assertEquals($domainEvent->nestedEvent()->eventVersion(), $reader->eventStringValue('nested_event', 'event_version'));
        $this->assertEquals($domainEvent->nestedEvent()->eventVersion(), $reader->eventStringValue('/nested_event/event_version'));
        $this->assertEquals($domainEvent->nestedEvent()->eventVersion(), $reader->eventIntegerValue('nested_event', 'event_version'));
        $this->assertEquals($domainEvent->nestedEvent()->eventVersion(), $reader->eventIntegerValue('/nested_event/event_version'));
        $this->assertEquals($domainEvent->nestedEvent()->id(), $reader->eventStringValue('nested_event', 'id'));
        $this->assertEquals($domainEvent->nestedEvent()->id(), $reader->eventStringValue('/nested_event/id'));
        $this->assertEquals($domainEvent->nestedEvent()->id(), $reader->eventLongValue('nested_event', 'id'));
        $this->assertEquals($domainEvent->nestedEvent()->id(), $reader->eventLongValue('/nested_event/id'));
        $this->assertEquals($domainEvent->nestedEvent()->name(), $reader->eventStringValue('nested_event', 'name'));
        $this->assertEquals($domainEvent->nestedEvent()->name(), $reader->eventStringValue('/nested_event/name'));
        $this->assertEquals($domainEvent->nestedEvent()->occurredOn()->getTimestamp(), $reader->eventStringValue('nested_event', 'occurred_on'));
        $this->assertEquals($domainEvent->nestedEvent()->occurredOn()->getTimestamp(), $reader->eventStringValue('/nested_event/occurred_on'));
        $this->assertEquals($domainEvent->nestedEvent()->occurredOn(), $reader->eventDateValue('nested_event', 'occurred_on'));
        $this->assertEquals($domainEvent->nestedEvent()->occurredOn(), $reader->eventDateValue('/nested_event/occurred_on'));
        $this->assertEquals($domainEvent->occurredOn()->getTimestamp(), $reader->eventStringValue('occurred_on'));
        $this->assertEquals($domainEvent->occurredOn()->getTimestamp(), $reader->eventStringValue('/occurred_on'));
        $this->assertEquals($domainEvent->occurredOn(), $reader->eventDateValue('occurred_on'));
        $this->assertEquals($domainEvent->occurredOn(), $reader->eventDateValue('/occurred_on'));
    }

    public function testDotNotation()
    {
        $domainEvent = new TestableNavigableDomainEvent(100, 'testing');

        $notification = new Notification(1, $domainEvent);

        $serializer = NotificationSerializer::instance();

        $serializedNotification = $serializer->serialize($notification);

        $reader = NotificationReader::fromString($serializedNotification);

        $this->assertEquals($domainEvent->nestedEvent()->eventVersion(), $reader->eventStringValue('nested_event.event_version'));
        $this->assertEquals($domainEvent->nestedEvent()->eventVersion(), $reader->eventIntegerValue('nested_event.event_version'));
    }

    public function testReadBogusProperties()
    {
        $domainEvent = new TestableNavigableDomainEvent(100, 'testing');

        $notification = new Notification(1, $domainEvent);

        $serializer = NotificationSerializer::instance();

        $serializedNotification = $serializer->serialize($notification);

        $reader = NotificationReader::fromString($serializedNotification);

        $mustThrow = false;

        try {
            $reader->eventStringValue('event_version.version');
        } catch (Exception $e) {
            $mustThrow = true;
        }

        $this->assertTrue($mustThrow);
    }

    public function testReadNullProperties()
    {
        $domainEvent = new TestableNullPropertyDomainEvent(100, 'testingNulls');

        $notification = new Notification(1, $domainEvent);

        $serializer = NotificationSerializer::instance();

        $serializedNotification = $serializer->serialize($notification);

        $reader = NotificationReader::fromString($serializedNotification);

        $this->assertNull($reader->eventStringValue('text_must_be_null'));
        $this->assertNull($reader->eventStringValue('text_must_be_null_2'));
        $this->assertNull($reader->eventIntegerValue('number_must_be_null'));
        $this->assertNull($reader->eventStringValue('nested.nested_text_must_be_null'));
        $this->assertNull($reader->eventStringValue('null_nested.nested_text_must_be_null'));
        $this->assertNull($reader->eventStringValue('nested.nested_deeply.nested_deeply_text_must_be_null'));
        $this->assertNull($reader->eventStringValue('nested.nested_deeply.nested_deeply_text_must_be_null2'));
        $this->assertNull($reader->eventStringValue('nested.null_nested_deeply.nested_deeply_text_must_be_null'));
        $this->assertNull($reader->eventStringValue('nested.null_nested_deeply.nested_deeply_text_must_be_null2'));
    }
}
