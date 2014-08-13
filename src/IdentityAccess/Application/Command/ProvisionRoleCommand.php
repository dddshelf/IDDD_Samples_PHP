<?php

namespace SaasOvation\IdentityAccess\Application\Command;

class ProvisionRoleCommand
{
    private $description;
    private $tenantId;
    private $roleName;
    private $supportsNesting;

    public function __construct($tenantId, $roleName, $description, $supportsNesting)
    {
        $this->description = $description;
        $this->roleName = $roleName;
        $this->supportsNesting = $supportsNesting;
        $this->tenantId = $tenantId;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getTenantId()
    {
        return $this->tenantId;
    }

    public function setTenantId($tenantId)
    {
        $this->tenantId = $tenantId;
    }

    public function getRoleName()
    {
        return $this->roleName;
    }

    public function setRoleName($roleName)
    {
        $this->roleName = $roleName;
    }

    public function isSupportsNesting()
    {
        return $this->supportsNesting;
    }

    public function setSupportsNesting($supportsNesting)
    {
        $this->supportsNesting = $supportsNesting;
    }
}
