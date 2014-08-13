<?php

namespace SaasOvation\Common\Test\Port\Adapter\Persistence\EventSourcing\MySQL;

use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use PHPUnit_Framework_TestCase;
use Rhumsaa\Uuid\Uuid;
use SaasOvation\Common\Domain\Model\DomainEventPublisher;
use SaasOvation\Common\Event\Sourcing\EventStore;
use SaasOvation\Common\Event\Sourcing\EventStoreAppendException;
use SaasOvation\Common\Event\Sourcing\EventStoreException;
use SaasOvation\Common\Event\Sourcing\EventStreamId;
use SaasOvation\Common\Port\Adapter\Persistence\EventSourcing\InMemory\InMemoryEventStore;
use SaasOvation\Common\Port\Adapter\Persistence\EventSourcing\LevelDB\LevelDBEventStore;
use SaasOvation\Common\Port\Adapter\Persistence\EventSourcing\MySQL\MySQLPDOEventStore;
use SaasOvation\Common\Test\BuildsServiceContainer;
use SaasOvation\Common\Test\Event\TestableDomainEvent;

class MySQLPDOEventStoreTest extends PHPUnit_Framework_TestCase
{
    use BuildsServiceContainer;

    /**
     * @var EventStore
     */
    private $eventStore;

    public function testConnectAndClose()
    {
        $this->assertNotNull($this->eventStore);
    }

    public function testAppend()
    {
        $this->assertNotNull($this->eventStore);

        $eventId = new EventStreamId(Uuid::uuid4()->toString());

        for ($idx = 1; $idx <= 2; ++$idx) {
            $this->eventStore->appendWith(
                $eventId->withStreamVersion($idx),
                new ArrayCollection([new TestableDomainEvent($idx, 'Name: ' . $idx)])
            );
        }

        $eventStream = $this->eventStore->fullEventStreamFor($eventId);

        $this->assertEquals(2, $eventStream->version());
        $this->assertEquals(2, $eventStream->events()->count());

        for ($idx = 1; $idx <= 2; ++$idx) {
            $domainEvent = $eventStream->events()->get($idx - 1);

            $this->assertEquals($idx, $domainEvent->id());
        }
    }

    public function testAppendWrongVersion()
    {
        $this->assertNotNull($this->eventStore);

        $eventId = new EventStreamId(Uuid::uuid4()->toString());

        for ($idx = 1; $idx <= 10; ++$idx) {
            $this->eventStore->appendWith(
                $eventId->withStreamVersion($idx),
                new ArrayCollection([new TestableDomainEvent($idx, 'Name: ' . $idx)])
            );
        }

        $eventStream = $this->eventStore->fullEventStreamFor($eventId);

        $this->assertEquals(10, $eventStream->version());
        $this->assertEquals(10, $eventStream->events()->count());

        $this->setExpectedException('\SaasOvation\Common\Event\Sourcing\EventStoreAppendException');

        $this->eventStore->appendWith(
            $eventId->withStreamVersion(8),
            new ArrayCollection([new TestableDomainEvent(11, 'Name: ' . 11)])
        );
    }

    public function testEventsSince()
    {
        $this->assertNotNull($this->eventStore);

        $eventId = new EventStreamId(Uuid::uuid4()->toString());

        for ($idx = 1; $idx <= 10; ++$idx) {
            $this->eventStore->appendWith(
                $eventId->withStreamVersion($idx),
                new ArrayCollection([new TestableDomainEvent($idx, 'Name: ' . $idx)])
            );
        }

        $loggedEvents = $this->eventStore->eventsSince($this->greatestEventId() - 8);

        $this->assertEquals(8, $loggedEvents->count());
    }

    public function testEventStreamSince()
    {
        $this->assertNotNull($this->eventStore);

        $eventId = new EventStreamId(Uuid::uuid4()->toString());

        for ($idx = 1; $idx <= 10; ++$idx) {
            $this->eventStore->appendWith(
                $eventId->withStreamVersion($idx),
                new ArrayCollection([new TestableDomainEvent($idx, 'Name: ' . $idx)])
            );
        }

        for ($idx = 10; $idx >= 1; --$idx) {
            $eventStream = $this->eventStore->eventStreamSince($eventId->withStreamVersion($idx));

            $this->assertEquals(10, $eventStream->version());
            $this->assertEquals(10 - $idx + 1, $eventStream->events()->count());

            $domainEvent = $eventStream->events()->get(0);

            $this->assertEquals($idx, $domainEvent->id());
        }

        try {
            $this->eventStore->eventStreamSince($eventId->withStreamVersion(11));

            $this->fail('Should have thrown an exception.');

        } catch (EventStoreException $e) {
            // good
        }
    }

    public function testFullEventStreamForStreamName()
    {
        $this->assertNotNull($this->eventStore);

        $events = new ArrayCollection();
        $eventId = new EventStreamId(Uuid::uuid4()->toString());

        for ($idx = 1; $idx <= 3; ++$idx) {
            $this->eventStore->appendWith(
                $eventId->withStreamVersion($idx),
                new ArrayCollection([new TestableDomainEvent($idx, 'Name: ' . $idx)])
            );
        }

        $eventStream = $this->eventStore->fullEventStreamFor($eventId);

        $this->assertEquals(3, $eventStream->version());
        $this->assertEquals(3, $eventStream->events()->count());

        $events->clear();
        $events->add(new TestableDomainEvent(4, 'Name: ' . 4));

        $this->eventStore->appendWith($eventId->withStreamVersion(4), $events);

        $eventStream = $this->eventStore->fullEventStreamFor($eventId);

        $this->assertEquals(4, $eventStream->version());
        $this->assertEquals(4, $eventStream->events()->count());

        for ($idx = 1; $idx <= 4; ++$idx) {
            $domainEvent = $eventStream->events()->get($idx - 1);

            $this->assertEquals($idx, $domainEvent->id());
        }
    }

    protected function setUp()
    {
        parent::setUp();

        DomainEventPublisher::instance()->reset();

        $this->buildAndCompileServiceContainer(
            [
                __DIR__ . '/../../../../../../Resources/config',
            ],
            [
                'common.xml',
                'common-doctrine.xml',
            ]
        );

        $this->eventStore = $this->container->get('mysqlPdoEventStore');
    }

    protected function tearDown()
    {
        $this->eventStore->purge();

        $this->eventStore->close();

        parent::tearDown();
    }

    private function greatestEventId()
    {
        $dataSource = $this->container->get('eventStoreDataSource');
        $greatestEventId = 0;

        try {
            $statement = $dataSource->prepare('SELECT MAX(event_id) from tbl_es_event_store');
            $statement->execute();
            $greatestEventId = (int) $statement->fetchColumn();
        } catch (Exception $t) {

        } finally {
            $statement->closeCursor();
        }

        return $greatestEventId;
    }
}
