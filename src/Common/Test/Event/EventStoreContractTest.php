<?php

namespace SaasOvation\Common\Test\Event;

use SaasOvation\Common\Test\CommonTestCase;

class EventStoreContractTest extends CommonTestCase
{
    public function testAllStoredEventsBetween()
    {
        $eventStore = $this->eventStore();

        $totalEvents = $eventStore->countStoredEvents();

        $this->assertEquals($totalEvents, $eventStore->allStoredEventsBetween(1, $totalEvents)->count());

        $this->assertEquals(10, $eventStore->allStoredEventsBetween($totalEvents - 9, $totalEvents)->count());
    }

    public function testAllStoredEventsSince()
    {
        $eventStore = $this->eventStore();

        $totalEvents = $eventStore->countStoredEvents();

        $this->assertEquals($totalEvents, $eventStore->allStoredEventsSince(0)->count());

        $this->assertEquals(0, $eventStore->allStoredEventsSince($totalEvents)->count());

        $this->assertEquals(10, $eventStore->allStoredEventsSince($totalEvents - 10)->count());
    }

    public function testAppend()
    {
        $eventStore = $this->eventStore();

        $numberOfEvents = $eventStore->countStoredEvents();

        $domainEvent = new TestableDomainEvent(10001, 'testDomainEvent');

        $storedEvent = $eventStore->append($domainEvent);

        $this->assertTrue($eventStore->countStoredEvents() > $numberOfEvents);
        $this->assertEquals($eventStore->countStoredEvents(), $numberOfEvents + 1);

        $this->assertNotNull($storedEvent);

        $reconstitutedDomainEvent = $storedEvent->toDomainEvent();

        $this->assertNotNull($reconstitutedDomainEvent);
        $this->assertEquals($domainEvent->id(), $reconstitutedDomainEvent->id());
        $this->assertEquals($domainEvent->name(), $reconstitutedDomainEvent->name());
        $this->assertEquals($domainEvent->occurredOn(), $reconstitutedDomainEvent->occurredOn());
    }

    public function testCountStoredEvents()
    {
        $eventStore = $this->eventStore();

        $numberOfEvents = $eventStore->countStoredEvents();

        $lastDomainEvent = null;

        for ($idx = 0; $idx < 10; ++$idx) {
            $domainEvent = new TestableDomainEvent(10001 + $idx, 'testDomainEvent' . $idx);

            $lastDomainEvent = $domainEvent;

            $eventStore->append($domainEvent);
        }

        $this->assertEquals($numberOfEvents + 10, $eventStore->countStoredEvents());

        $numberOfEvents = $eventStore->countStoredEvents();

        $this->assertEquals(1, $eventStore->allStoredEventsBetween($numberOfEvents, $numberOfEvents + 1000)->count());

        $storedEvent = $eventStore->allStoredEventsBetween($numberOfEvents, $numberOfEvents)->get(0);

        $this->assertNotNull($storedEvent);

        $reconstitutedDomainEvent = $storedEvent->toDomainEvent();

        $this->assertNotNull($reconstitutedDomainEvent);
        $this->assertEquals($lastDomainEvent->id(), $reconstitutedDomainEvent->id());
        $this->assertEquals($lastDomainEvent->name(), $reconstitutedDomainEvent->name());
        $this->assertEquals($lastDomainEvent->occurredOn(), $reconstitutedDomainEvent->occurredOn());
    }

    public function testStoredEvent()
    {
        $eventStore = $this->eventStore();

        $domainEvent = new TestableDomainEvent(10001, 'testDomainEvent');

        $storedEvent = $eventStore->append($domainEvent);

        $this->assertNotNull($storedEvent);

        $reconstitutedDomainEvent = $storedEvent->toDomainEvent();

        $this->assertNotNull($reconstitutedDomainEvent);
        $this->assertEquals($domainEvent->id(), $reconstitutedDomainEvent->id());
        $this->assertEquals($domainEvent->name(), $reconstitutedDomainEvent->name());
        $this->assertEquals($domainEvent->occurredOn(), $reconstitutedDomainEvent->occurredOn());
    }

    private function eventStore()
    {
        $eventStore = new MockEventStore();

        $this->assertNotNull($eventStore);

        return $eventStore;
    }
}
