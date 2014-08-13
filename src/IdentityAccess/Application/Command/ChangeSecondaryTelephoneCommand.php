<?php

namespace SaasOvation\IdentityAccess\Application\Command;

class ChangeSecondaryTelephoneCommand
{
    private $tenantId;
    private $username;
    private $telephone;

    public function __construct($tenantId, $username, $telephone)
    {
        $this->tenantId = $tenantId;
        $this->username = $username;
        $this->telephone = $telephone;
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

    public function getTelephone()
    {
        return $this->telephone;
    }

    public function setTelephone($telephone)
    {
        $this->telephone = $telephone;
    }
}
