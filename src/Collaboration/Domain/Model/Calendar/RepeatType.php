<?php

namespace SaasOvation\Collaboration\Domain\Model\Calendar;

use SaasOvation\Common\Enum;

abstract class RepeatType extends Enum
{
    public function isDaily()
    {
        return false;
    }

    public function isDoesNotRepeat()
    {
        return false;
    }

    public function isMonthly()
    {
        return false;
    }

    public function isWeekly()
    {
        return false;
    }

    public function isYearly()
    {
        return false;
    }
}