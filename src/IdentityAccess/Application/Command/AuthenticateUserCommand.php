<?php

namespace SaasOvation\IdentityAccess\Application\Command;

class AuthenticateUserCommand
{
    private $tenantId;
    private $username;
    private $password;

    public function __construct($tenantId, $aUsername, $aPassword)
    {
        $this->tenantId = $tenantId;
        $this->username = $aUsername;
        $this->password = $aPassword;
    }

    public function getTenantId() {
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

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }
}
