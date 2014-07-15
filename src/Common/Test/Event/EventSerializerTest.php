<?php

namespace SaasOvation\Common\Test\Event;

use PHPUnit_Framework_TestCase;
use SaasOvation\Common\Event\EventSerializer;

class EventSerializerTest extends PHPUnit_Framework_TestCase
{
    public function testDefaultFormat()
    {
        $serializer = EventSerializer::instance();

        $serializedEvent = $serializer->serialize(new TestableDomainEvent(1, null));

        $this->assertContains('id', $serializedEvent);
        $this->assertContains('occurred_on', $serializedEvent);
        $this->assertNotContains("\n", $serializedEvent);
        $this->assertContains('null', $serializedEvent);
    }

    public function testDeserializeDefault()
    {
        $serializer = EventSerializer::instance();

        $serializedEvent = $serializer->serialize(new TestableDomainEvent(1, null));

        $event = $serializer->deserialize($serializedEvent, TestableDomainEvent::class);

        $this->assertContains('null', $serializedEvent);
        $this->assertEquals(1, $event->id());
        $this->assertNull($event->name());
        $this->assertNotNull($event->occurredOn());
    }
}
