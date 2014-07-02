<?php

namespace SaasOvation\Collaboration\Domain\Model\Calendar\RepeatType;

use SaasOvation\Collaboration\Domain\Model\Calendar\RepeatType;

class DoesNotRepeat extends RepeatType
{
    public function isDoesNotRepeat()
    {
        return true;
    }
}