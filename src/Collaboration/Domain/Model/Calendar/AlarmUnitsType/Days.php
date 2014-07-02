<?php

namespace SaasOvation\Collaboration\Domain\Model\Calendar\AlarmUnitsType;

use SaasOvation\Collaboration\Domain\Model\Calendar\AlarmUnitsType;

class Days extends AlarmUnitsType
{
    public function isDays()
    {
        return true;
    }
}