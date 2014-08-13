<?php

namespace SaasOvation\IdentityAccess\Application\Representation;

use SaasOvation\IdentityAccess\Domain\Model\Identity\User;

class UserInRoleRepresentation
{
    private $emailAddress;
    private $firstName;
    private $lastName;
    private $role;
    private $tenantId;
    private $username;

    public function __construct(User $aUser, $aRole)
    {
        $this->initializeFrom($aUser, $aRole);
    }

    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function getTenantId()
    {
        return $this->tenantId;
    }

    public function getUsername()
    {
        return $this->username;
    }

    private function initializeFrom(User $aUser, $aRole)
    {
        $desc = $aUser->userDescriptor();
        $this->setEmailAddress($desc->emailAddress());
        $this->setFirstName($aUser->person()->name()->firstName());
        $this->setLastName($aUser->person()->name()->lastName());
        $this->setRole($aRole);
        $this->setTenantId($desc->tenantId()->id());
        $this->setUsername($desc->username());
    }

    private function setEmailAddress($emailAddress)
    {
        $this->emailAddress = $emailAddress;
    }

    private function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    private function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    private function setRole($aRole)
    {
        $this->role = $aRole;
    }

    private function setTenantId($aTenantId)
    {
        $this->tenantId = $aTenantId;
    }

    private function setUsername($aUsername)
    {
        $this->username = $aUsername;
    }
}
