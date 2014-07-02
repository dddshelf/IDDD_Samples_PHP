<?php

namespace SaasOvation\Collaboration\Domain\Model\Calendar\AlarmUnitsType;

use SaasOvation\Collaboration\Domain\Model\Calendar\AlarmUnitsType;

class Minutes extends AlarmUnitsType
{
    public function isMinutes()
    {
        return true;
    }
}