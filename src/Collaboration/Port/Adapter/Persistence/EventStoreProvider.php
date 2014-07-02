<?php

namespace SaasOvation\Collaboration\Port\Adapter\Persistence;

use SaasOvation\Common\Event\Sourcing\EventStore;
use SaasOvation\Common\Port\Adapter\Persistence\EventSourcing\InMemory\InMemoryEventStore;
use SaasOvation\Common\Port\Adapter\Persistence\EventSourcing\LevelDB\LevelDBEventStore;
use SaasOvation\Common\Port\Adapter\Persistence\EventSourcing\MySQL\MySQLPDOEventStore;

class EventStoreProvider
{
    private static $FOR_LEVELDB = false;
    private static $FOR_MYSQL   = false;
    private static $FOR_MEMORY  = true;

    /**
     * @var EventStore
     */
    private $eventStore;

    public static function instance()
    {
        $class = get_called_class();

        return new $class();
    }

    public function eventStore()
    {
        return $this->eventStore;
    }

    protected function __construct()
    {
        $this->initializeLevelDB();
        $this->initializeMySQL();
        $this->initializeInMemory();
    }

    private function initializeLevelDB()
    {
        if (self::$FOR_LEVELDB) {
            $this->eventStore = LevelDBEventStore::instance('/data/leveldb/iddd_collaboration_es');
        }
    }

    private function initializeMySQL()
    {
        if (self::$FOR_MYSQL) {
            $this->eventStore = MySQLPDOEventStore::instance();
        }
    }

    private function initializeInMemory()
    {
        if (self::$FOR_MEMORY) {
            $this->eventStore = InMemoryEventStore::instance();
        }
    }
}
