<?php

namespace SaasOvation\Common\Domain\Model\Process;

use MabeEnum\Enum;

class ProcessCompletionType extends Enum
{
    const NotCompleted          = 1;
    const CompletedNormally     = 2;
    const TimedOut              = 3;
}
