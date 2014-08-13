<?php

namespace SaasOvation\IdentityAccess\Application\Representation;

use SaasOvation\IdentityAccess\Domain\Model\Identity\TenantId;
use SaasOvation\IdentityAccess\Domain\Model\Identity\User;

class UserRepresentation
{
    /**
     * @var string
     */
    private $emailAddress;

    /**
     * @var boolean
     */
    private $enabled;

    /**
     * @var string
     */
    private $firstName;

    /**
     * @var string
     */
    private $lastName;

    /**
     * @var TenantId
     */
    private $tenantId;

    /**
     * @var string
     */
    private $username;

    public function __construct(User $aUser)
    {
        $this->initializeFrom($aUser);
    }

    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    public function getEnabled()
    {
        return $this->enabled;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function getTenantId()
    {
        return $this->tenantId->id();
    }

    public function getUsername()
    {
        return $this->username;
    }

    private function initializeFrom(User $aUser)
    {
        $this->emailAddress = $aUser->person()->emailAddress()->address();
        $this->enabled = $aUser->isEnabled();
        $this->firstName = $aUser->person()->name()->firstName();
        $this->lastName = $aUser->person()->name()->lastName();
        $this->tenantId = $aUser->tenantId();
        $this->username = $aUser->username();
    }
}
