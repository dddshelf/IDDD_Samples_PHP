<?php

namespace SaasOvation\Common\Domain\Model;

use SaasOvation\Common\AssertionConcern;

class IdentifiedDomainObject extends AssertionConcern
{
    /**
     * @var int
     */
    private $id;

    protected function __construct()
    {
        $this->setId(-1);
    }

    protected function id()
    {
        return $this->id;
    }

    private function setId($anId)
    {
        $this->id = $anId;
    }
}
