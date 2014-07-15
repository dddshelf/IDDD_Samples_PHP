<?php

namespace SaasOvation\Common\Test\Port\Adapter\Persistence\LevelDB;

use SaasOvation\Common\Event\EventStore;
use SaasOvation\Common\Port\Adapter\Persistence\LevelDB\LevelDBEventStore;
use SaasOvation\Common\Port\Adapter\Persistence\LevelDB\LevelDBUnitOfWork;
use SaasOvation\Common\Test\Event\TestableDomainEvent;
use LevelDB;

class LevelDBEventStoreTest extends LevelDBTest
{
    /**
     * @var EventStore
     */
    private $eventStore;

    public function testAllStoredEventsBetween()
    {
        $eventStore = $this->eventStore();

        $totalEvents = $eventStore->countStoredEvents();

        $this->assertCount($totalEvents, $eventStore->allStoredEventsBetween(1, $totalEvents));

        $this->assertCount(10, $eventStore->allStoredEventsBetween($totalEvents - 9, $totalEvents));
    }

    public function testAllStoredEventsSince()
    {
        $eventStore = $this->eventStore();

        $totalEvents = $eventStore->countStoredEvents();

        $this->assertCount($totalEvents, $eventStore->allStoredEventsSince(0));
        $this->assertCount(0, $eventStore->allStoredEventsSince($totalEvents));
        $this->assertCount(10, $eventStore->allStoredEventsSince($totalEvents - 10));
    }

    public function testAppend()
    {
        $eventStore = $this->eventStore();

        $numberOfEvents = $eventStore->countStoredEvents();

        $domainEvent = new TestableDomainEvent(10001, 'testDomainEvent');

        $storedEvent = $eventStore->append($domainEvent);

        $this->assertTrue($eventStore->countStoredEvents() > $numberOfEvents);
        $this->assertEquals($numberOfEvents + 1, $eventStore->countStoredEvents());

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

        LevelDBUnitOfWork::current()->commit();

        $this->assertEquals($numberOfEvents + 10, $eventStore->countStoredEvents());

        $numberOfEvents = $eventStore->countStoredEvents();

        $this->assertCount(1, $eventStore->allStoredEventsBetween($numberOfEvents, $numberOfEvents + 1000));

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

    protected function setUp()
    {
        parent::setUp();

        $this->eventStore = new LevelDBEventStore(self::$TEST_DATABASE);

        $this->assertNotNull($this->eventStore);

        $this->seedEventStore();
    }

    protected function tearDown()
    {
        $this->eventStore()->close();
        $this->eventStore = null;

        parent::tearDown();
    }

    private function eventStore()
    {
        return $this->eventStore;
    }

    private function seedEventStore()
    {
        $numberOfStoredEvents = $this->millisecondWithinSecond();

        if ($numberOfStoredEvents < 21) {
            $numberOfStoredEvents = 21;
        }

        $startId = 991;

        for ($idx = 0; $idx < $numberOfStoredEvents; ++$idx) {
            $domainEvent = new TestableDomainEvent($startId + $idx, 'testDomainEvent' . $idx);

            $this->eventStore->append($domainEvent);
        }

        LevelDBUnitOfWork::current()->commit();
    }

    private function millisecondWithinSecond()
    {
        return round(microtime(true) * 1000) % 1000;
    }
}
