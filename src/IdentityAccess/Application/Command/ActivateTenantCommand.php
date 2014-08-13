<?php

namespace SaasOvation\IdentityAccess\Application\Command;

class ActivateTenantCommand
{
    /**
     * @var string
     */
    private $tenantId;

    public function __construct($tenantId)
    {
        $this->tenantId = $tenantId;
    }

    public function getTenantId()
    {
        return $this->tenantId;
    }

    public function setTenantId($tenantId)
    {
        $this->tenantId = $tenantId;
    }
}
