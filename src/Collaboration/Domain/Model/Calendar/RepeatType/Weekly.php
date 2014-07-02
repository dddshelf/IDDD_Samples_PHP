<?php

namespace SaasOvation\Collaboration\Domain\Model\Calendar\RepeatType;

use SaasOvation\Collaboration\Domain\Model\Calendar\RepeatType;

class Weekly extends RepeatType
{
    public function isWeekly()
    {
        return true;
    }
}