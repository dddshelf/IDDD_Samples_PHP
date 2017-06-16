<?php

namespace SaasOvation\IdentityAccess\Domain\Model\Access;

use DateTimeImmutable;
use SaasOvation\Common\Domain\Model\DomainEvent;
use SaasOvation\Common\Domain\Model\ImplementsDomainEvent;
use SaasOvation\IdentityAccess\Domain\Model\Identity\TenantId;

class UserAssignedToRole implements DomainEvent
{
    use ImplementsDomainEvent;
    
    /**
     * @var string
     */
    private $emailAddress;

    /**
     * @var string
     */
    private $firstName;

    /**
     * @var string
     */
    private $lastName;

    /**
     * @var string
     */
    private $roleName;

    /**
     * @var TenantId
     */
    private $tenantId;

    /**
     * @var string
     */
    private $username;

    public function __construct(
        TenantId $aTenantId,
        $aRoleName,
        $aUsername,
        $aFirstName,
        $aLastName,
        $anEmailAddress
    ) {
        $this->emailAddress = $anEmailAddress;
        $this->firstName = $aFirstName;
        $this->lastName = $aLastName;
        $this->occurredOn = new DateTimeImmutable();
        $this->roleName = $aRoleName;
        $this->tenantId = $aTenantId;
        $this->username = $aUsername;
    }

    public function emailAddress()
    {
        return $this->emailAddress;
    }

    public function firstName()
    {
        return $this->firstName;
    }

    public function lastName()
    {
        return $this->lastName;
    }

    public function roleName()
    {
        return $this->roleName;
    }

    public function tenantId()
    {
        return $this->tenantId;
    }

    public function username()
    {
        return $this->username;
    }
}
