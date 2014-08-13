<?php

namespace SaasOvation\IdentityAccess\Application\Command;

class AssignUserToRoleCommand
{
    private $tenantId;
    private $username;
    private $roleName;

    public function __construct($tenantId, $aUsername, $aRoleName)
    {
        $this->tenantId = $tenantId;
        $this->username = $aUsername;
        $this->roleName = $aRoleName;
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

    public function getRoleName()
    {
        return $this->roleName;
    }

    public function setRoleName($rolename)
    {
        $this->roleName = $rolename;
    }
}
