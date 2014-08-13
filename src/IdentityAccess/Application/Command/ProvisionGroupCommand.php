<?php

namespace SaasOvation\IdentityAccess\Application\Command;

class ProvisionGroupCommand
{
    private $description;
    private $groupName;
    private $tenantId;

    public function __construct($tenantId, $groupName, $description)
    {
        $this->description = $description;
        $this->groupName = $groupName;
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

    public function getGroupName() 
    {
        return $this->groupName;
    }

    public function setGroupName($groupName) 
    {
        $this->groupName = $groupName;
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
