<?php

namespace SaasOvation\Collaboration\Domain\Model\Calendar;

use SaasOvation\Common\Enum;

abstract class AlarmUnitsType extends Enum
{
    public function isDays()
    {
        return false;
    }

    public function isHours()
    {
        return false;
    }

    public function isMinutes()
    {
        return false;
    }
}