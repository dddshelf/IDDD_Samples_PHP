<?php

namespace SaasOvation\IdentityAccess\Application\Command;

class ChangeEmailAddressCommand
{
    private $tenantId;
    private $username;
    private $emailAddress;

    public function __construct($tenantId, $username, $emailAddress)
    {
        $this->tenantId = $tenantId;
        $this->username = $username;
        $this->emailAddress = $emailAddress;
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

    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    public function setEmailAddress($emailAddress)
    {
        $this->emailAddress = $emailAddress;
    }
}
