<?php

namespace SaasOvation\Common\Domain\Model;

use RuntimeException;

class ConcurrencySafeEntity extends Entity
{
    /**
     * @var int
     */
    private $concurrencyVersion;

    public function concurrencyVersion()
    {
        return $this->concurrencyVersion;
    }

    public function setConcurrencyVersion($aVersion)
    {
        $this->failWhenConcurrencyViolation($aVersion);
        $this->concurrencyVersion = $aVersion;
    }

    public function failWhenConcurrencyViolation($aVersion)
    {
        if ($aVersion != $this->concurrencyVersion()) {
            throw new RuntimeException(
                'Concurrency Violation: Stale data detected. Entity was already modified.'
            );
        }
    }
}
