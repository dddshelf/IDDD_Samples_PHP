<?php

namespace SaasOvation\Common\Test\Port\Adapter\Persistence\LevelDB;

use LevelDB;
use PHPUnit_Framework_TestCase;
use SaasOvation\Common\Event\EventStore;
use SaasOvation\Common\Port\Adapter\Persistence\LevelDB\LevelDBProvider;
use SaasOvation\Common\Port\Adapter\Persistence\LevelDB\LevelDBPublishedNotificationTrackerStore;
use SaasOvation\Common\Port\Adapter\Persistence\LevelDB\LevelDBUnitOfWork;
use SaasOvation\Common\Test\Event\MockEventStore;
use SaasOvation\Common\Notification\NotificationLogFactory;
use SaasOvation\Common\Notification\PublishedNotificationTracker;

class LevelDBPublishedNotificationTrackerStoreTest extends PHPUnit_Framework_TestCase
{
    private static $TEST_DATABASE = '/data/leveldb/iddd_common_test';

    /**
     * @var LevelDB
     */
    private $database;

    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @var LevelDBPublishedNotificationTrackerStore
     */
    private $publishedNotificationTrackerStore;

    public function testTrackMostRecentPublishedNotification()
    {
        $factory = new NotificationLogFactory($this->eventStore);
        $log = $factory->createCurrentNotificationLog();
        
        $this->publishedNotificationTrackerStore->trackMostRecentPublishedNotification(
            new PublishedNotificationTracker('saasOvation_test'),
            $log->notifications()
        );
        
        LevelDBUnitOfWork::current()->commit();
        
        $tracker = $this->publishedNotificationTrackerStore->publishedNotificationTracker();
        
        $notifications = $log->notifications()->count();
        
        $this->assertNotNull($tracker);
        $this->assertEquals(
            $log->notifications()->get($notifications - 1)->notificationId(),
            $tracker->mostRecentPublishedNotificationId()
        );
    }

    protected function setUp()
    {
        $this->database = LevelDBProvider::instance()->databaseFrom(self::$TEST_DATABASE);

        $this->eventStore = new MockEventStore();

        $this->assertNotNull($this->eventStore);

        $this->publishedNotificationTrackerStore = new LevelDBPublishedNotificationTrackerStore(
            self::$TEST_DATABASE,
            'saasOvation_test'
        );
    }

    protected function tearDown()
    {
        LevelDBProvider::instance()->close(self::$TEST_DATABASE);
        LevelDB::destroy(self::$TEST_DATABASE);
    }
}
