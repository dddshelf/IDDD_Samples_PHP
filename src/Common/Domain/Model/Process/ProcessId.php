<?php

namespace SaasOvation\Common\Domain\Model\Process;

use Rhumsaa\Uuid\Uuid;
use SaasOvation\Common\Domain\Model\AbstractId;

final class ProcessId extends AbstractId
{
    public static function existingProcessId($anId)
    {
        return new ProcessId($anId);
    }

    public static function newProcessId()
    {
        return new ProcessId(strtoupper(Uuid::uuid4()));
    }
}
