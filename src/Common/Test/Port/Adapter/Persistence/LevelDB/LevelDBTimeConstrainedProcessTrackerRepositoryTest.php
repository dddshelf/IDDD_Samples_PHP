<?php

namespace SaasOvation\Common\Test\Port\Adapter\Persistence\LevelDB;

use LevelDB;
use PHPUnit_Framework_TestCase;

use SaasOvation\Common\Domain\Model\DomainEvent;
use SaasOvation\Common\Domain\Model\DomainEventPublisher;
use SaasOvation\Common\Domain\Model\DomainEventSubscriber;
use SaasOvation\Common\Domain\Model\Process\ProcessId;
use SaasOvation\Common\Port\Adapter\Persistence\LevelDB\LevelDBProvider;
use SaasOvation\Common\Port\Adapter\Persistence\LevelDB\LevelDBTimeConstrainedProcessTrackerRepository;
use SaasOvation\Common\Port\Adapter\Persistence\LevelDB\LevelDBUnitOfWork;
use SaasOvation\Common\Test\Domain\Model\Process\TestableTimeConstrainedProcess;
use SaasOvation\Common\Test\Domain\Model\Process\TestableTimeConstrainedProcessTimedOut;
use SaasOvation\Common\Domain\Model\Process\TimeConstrainedProcessTrackerRepository;
use SaasOvation\Common\Domain\Model\Process\ProcessCompletionType;
use UnexpectedValueException;

class LevelDBTimeConstrainedProcessTrackerRepositoryTest extends PHPUnit_Framework_TestCase
{
    private static $TEST_DATABASE = '/data/leveldb/iddd_common_test';
    private static $TENANT_ID = '1234567890';

    /**
     * @var LevelDB
     */
    private $database;

    /**
     * @var TestableTimeConstrainedProcess
     */
    private $process;

    /**
     * @var boolean
     */
    private $received = false;

    /**
     * @var TimeConstrainedProcessTrackerRepository
     */
    private $trackerRepository;

    public function testCompletedProcess()
    {
        DomainEventPublisher::instance()->subscribe(new TestableTimeConstrainedProcessTimedOutSubscriber($this));

        $this->process = new TestableTimeConstrainedProcess(
            self::$TENANT_ID,
            ProcessId::newProcessId(),
            'Testable Time Constrained Process',
            5000
        );

        $tracker = $this->process->timeConstrainedProcessTracker();

        LevelDBUnitOfWork::start($this->database);
        $this->trackerRepository->save($tracker);
        LevelDBUnitOfWork::current()->commit();

        $this->process->confirm1();

        $this->assertFalse($this->received);
        $this->assertFalse($this->process->isCompleted());
        $this->assertFalse($this->process->didProcessingComplete());
        $this->assertEquals(ProcessCompletionType::NotCompleted(), $this->process->processCompletionType());

        $this->process->confirm2();

        $this->assertFalse($this->received);
        $this->assertTrue($this->process->isCompleted());
        $this->assertTrue($this->process->didProcessingComplete());
        $this->assertEquals(ProcessCompletionType::CompletedNormally(), $this->process->processCompletionType());
        $this->assertNull($this->process->timedOutDate());

        $tracker->informProcessTimedOut();

        $this->assertFalse($this->received);
        $this->assertFalse($this->process->isTimedOut());

        $this->assertFalse($this->trackerRepository->allTrackers($this->process->tenantId())->isEmpty());
        $this->assertTrue($this->trackerRepository->allTimedOutOf($this->process->tenantId())->isEmpty());
    }

    public function testTimedOutProcess()
    {
        $process1 = new TestableTimeConstrainedProcess(
            self::$TENANT_ID,
            ProcessId::newProcessId(),
            'Testable Time Constrained Process 1',
            1
        ); // forced timeout

        $tracker1 = $process1->timeConstrainedProcessTracker();

        $process2 = new TestableTimeConstrainedProcess(
            self::$TENANT_ID,
            ProcessId::newProcessId(),
            'Testable Time Constrained Process 2',
            5000
        );

        $tracker2 = $process2->timeConstrainedProcessTracker();

        LevelDBUnitOfWork::start($this->database);
        $this->trackerRepository->save($tracker1);
        $this->trackerRepository->save($tracker2);
        LevelDBUnitOfWork::current()->commit();

        sleep(1);

        $allTrackers = $this->trackerRepository->allTrackers($process1->tenantId());
        $allTimedOut = $this->trackerRepository->allTimedOut();

        $this->assertFalse($allTrackers->isEmpty());
        $this->assertEquals(2, $allTrackers->count());
        $this->assertFalse($allTimedOut->isEmpty());
        $this->assertEquals(1, $allTimedOut->count());
    }

    public function setReceived($value)
    {
        $this->received = $value;
    }

    public function process()
    {
        return $this->process;
    }

    protected function setUp()
    {
        $this->closeDatabase();

        $this->database = LevelDBProvider::instance()->databaseFrom(self::$TEST_DATABASE);

        $this->trackerRepository = new LevelDBTimeConstrainedProcessTrackerRepository(self::$TEST_DATABASE);
    }

    protected function tearDown()
    {
        $this->closeDatabase();
    }

    private function closeDatabase()
    {
        LevelDBProvider::instance()->close(self::$TEST_DATABASE);
        LevelDB::destroy(self::$TEST_DATABASE);
        $this->database = null;

        try {
            LevelDBUnitOfWork::current()->rollback();
        } catch (UnexpectedValueException $e) {

        }
    }
}
