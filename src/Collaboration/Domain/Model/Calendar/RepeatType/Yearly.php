<?php

namespace SaasOvation\Collaboration\Domain\Model\Calendar\RepeatType;

use SaasOvation\Collaboration\Domain\Model\Calendar\RepeatType;

class Yearly extends RepeatType
{
    public function isYearly()
    {
        return true;
    }
}