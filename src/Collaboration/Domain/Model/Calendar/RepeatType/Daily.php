<?php

namespace SaasOvation\Collaboration\Domain\Model\Calendar\RepeatType;

use SaasOvation\Collaboration\Domain\Model\Calendar\RepeatType;

class Daily extends RepeatType
{
    public function isDaily()
    {
        return true;
    }
}