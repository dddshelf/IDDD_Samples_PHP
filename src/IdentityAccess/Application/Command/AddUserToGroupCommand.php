<?php

namespace SaasOvation\IdentityAccess\Application\Command;

class AddUserToGroupCommand
{
    private $tenantId;
    private $groupName;
    private $username;
    
    public function __construct($tenantId, $groupName, $username)
    {
        $this->tenantId = $tenantId;
        $this->groupName = $groupName;
        $this->username = $username;
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

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username)
    {
        $this->username = $username;
    }
}
