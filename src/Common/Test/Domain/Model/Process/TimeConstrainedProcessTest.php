<?php

namespace SaasOvation\Common\Test\Domain\Model\Process;

use SaasOvation\Common\Domain\Model\DomainEventPublisher;
use SaasOvation\Common\Domain\Model\Process\ProcessCompletionType;
use SaasOvation\Common\Domain\Model\Process\ProcessId;
use SaasOvation\Common\Test\CommonTestCase;

class TimeConstrainedProcessTest extends CommonTestCase
{
    private static $TENANT_ID = '1234567890';

    /**
     * @var TestableTimeConstrainedProcess
     */
    private $process;

    /**
     * @var boolean
     */
    private $received = false;

    public function testCompletedProcess()
    {
        DomainEventPublisher::instance()->subscribe(new TestableTimeConstrainedProcessTimedOutSubscriber($this));

        $this->process = new TestableTimeConstrainedProcess(
            static::$TENANT_ID,
            ProcessId::newProcessId(),
            'Testable Time Constrained Process',
            5000
        );

        $tracker = $this->process->timeConstrainedProcessTracker();

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
    }

    public function setReceived($received)
    {
        $this->received = $received;
    }

    public function process()
    {
        return $this->process;
    }
}
