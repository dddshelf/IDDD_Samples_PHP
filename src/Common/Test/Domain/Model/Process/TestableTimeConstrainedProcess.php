<?php

namespace SaasOvation\Common\Test\Domain\Model\Process;

use SaasOvation\Common\Domain\Model\Process\AbstractProcess;
use SaasOvation\Common\Domain\Model\Process\ProcessCompletionType;
use SaasOvation\Common\Domain\Model\Process\ProcessId;

class TestableTimeConstrainedProcess extends AbstractProcess
{
    /**
     * @var boolean
     */
    private $confirm1;

    /**
     * @var boolean
     */
    private $confirm2;

    public function confirm1()
    {
        $this->confirm1 = true;

        $this->completeProcess(ProcessCompletionType::NotCompleted());
    }

    public function confirm2()
    {
        $this->confirm2 = true;

        $this->completeProcess(ProcessCompletionType::CompletedNormally());
    }

    protected function completenessVerified()
    {
        return $this->confirm1 && $this->confirm2;
    }

    protected function processTimedOutEventType()
    {
        return TestableTimeConstrainedProcessTimedOut::class;
    }
}
