<?php

namespace SaasOvation\Common\Test\Domain\Model\Process;

use SaasOvation\Common\Domain\Model\Process\ProcessId;
use SaasOvation\Common\Domain\Model\Process\ProcessTimedOut;

class TestableTimeConstrainedProcessTimedOut extends ProcessTimedOut
{
    public static function createFromTenantAndProcess($aTenantId, ProcessId $aProcessId)
    {
        return new static($aTenantId, $aProcessId, 0, 0);
    }
}
