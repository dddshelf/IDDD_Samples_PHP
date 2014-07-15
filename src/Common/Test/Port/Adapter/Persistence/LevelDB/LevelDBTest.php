<?php

namespace SaasOvation\Common\Test\Port\Adapter\Persistence\LevelDB;

use Exception;
use PHPUnit_Framework_TestCase;
use SaasOvation\Common\Domain\Model\DomainEventPublisher;
use LevelDB;
use SaasOvation\Common\Port\Adapter\Persistence\LevelDB\LevelDBProvider;
use SaasOvation\Common\Port\Adapter\Persistence\LevelDB\LevelDBUnitOfWork;

abstract class LevelDBTest extends PHPUnit_Framework_TestCase
{
    public static $TEST_DATABASE = "/data/leveldb/iddd_common_test";

    /**
     * @var LevelDB
     */
    private $database;

    protected function database()
    {
        return $this->database;
    }

    protected function setUp()
    {
        $this->database = LevelDBProvider::instance()->databaseFrom(self::$TEST_DATABASE);

        DomainEventPublisher::instance()->reset();
    }

    protected function tearDown()
    {
        LevelDBProvider::instance()->close(self::$TEST_DATABASE);
        LevelDB::destroy(self::$TEST_DATABASE);

        try {
            LevelDBUnitOfWork::current()->rollback();
        } catch (Exception $e) {
            // no-op
        }
    }
}
