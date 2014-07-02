<?php

namespace SaasOvation\Collaboration\Domain\Model\Calendar\RepeatType;

use SaasOvation\Collaboration\Domain\Model\Calendar\RepeatType;

class Monthy extends RepeatType
{
    public function isMonthly()
    {
        return true;
    }
}