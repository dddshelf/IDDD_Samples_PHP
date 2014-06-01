<?php

namespace SaasOvation\AgilePm\Domain\Model;

use SaasOvation\Common\AssertionConcern;

abstract class Entity extends AssertionConcern
{
    /**
     * @var int
     */
    private $concurrencyVersion;

    public function __construct()
    {
        parent::__construct();

        $this->setConcurrencyVersion(0);
    }

    /**
     * @return int
     */
    public function concurrencyVersion()
    {
        return $this->concurrencyVersion;
    }

    /**
     * @param int $aConcurrencyVersion
     */
    private function setConcurrencyVersion($aConcurrencyVersion)
    {
        $this->concurrencyVersion = (int) $aConcurrencyVersion;
    }
}
