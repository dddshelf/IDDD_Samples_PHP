<?php

namespace SaasOvation\Common\Domain\Model\Process;

interface TimeConstrainedProcessTrackerRepository
{
    public function add(TimeConstrainedProcessTracker $aTimeConstrainedProcessTracker);

    public function allTimedOut();

    public function allTimedOutOf($aTenantId);

    public function allTrackers($aTenantId);

    public function save(TimeConstrainedProcessTracker $aTimeConstrainedProcessTracker);

    public function trackerOfProcessId($aTenantId, ProcessId $aProcessId);
}
