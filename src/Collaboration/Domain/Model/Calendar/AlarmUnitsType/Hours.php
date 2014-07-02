<?php

namespace SaasOvation\Collaboration\Domain\Model\Calendar\AlarmUnitsType;

use SaasOvation\Collaboration\Domain\Model\Calendar\AlarmUnitsType;

class Hours extends AlarmUnitsType
{
    public function isHours()
    {
        return true;
    }
}