<?php

namespace SaasOvation\IdentityAccess\Application\Command;

class ChangeUserPersonalNameCommand
{
    private $tenantId;
    private $username;
    private $firstName;
    private $lastName;

    public function __construct($tenantId, $username, $aFirstName, $aLastName)
    {
        $this->tenantId = $tenantId;
        $this->username = $username;
        $this->firstName = $aFirstName;
        $this->lastName = $aLastName;
    }

    public function getTenantId() {
        return $this->tenantId;
    }

    public function setTenantId($tenantId) {
        $this->tenantId = $tenantId;
    }

    public function getUsername() {
        return $this->username;
    }

    public function setUsername($username) {
        $this->username = $username;
    }

    public function getFirstName() {
        return $this->firstName;
    }

    public function setFirstName($firstName) {
        $this->firstName = $firstName;
    }

    public function getLastName() {
        return $this->lastName;
    }

    public function setLastName($lastName) {
        $this->lastName = $lastName;
    }
}
