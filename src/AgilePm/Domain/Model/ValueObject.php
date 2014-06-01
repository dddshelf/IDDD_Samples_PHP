<?php

namespace SaasOvation\AgilePm\Domain\Model;

use SaasOvation\Common\AssertionConcern;

abstract class ValueObject extends AssertionConcern
{
    public function __construct()
    {
        parent::__construct();
    }
}
