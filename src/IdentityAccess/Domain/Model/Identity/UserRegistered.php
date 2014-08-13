<?php

namespace SaasOvation\IdentityAccess\Domain\Model\Identity;

use DateTimeImmutable;
use SaasOvation\Common\Domain\Model\DomainEvent;
use SaasOvation\Common\Domain\Model\ImplementsDomainEvent;

class UserRegistered implements DomainEvent
{
    use ImplementsDomainEvent;

    /**
     * @var EmailAddress
     */
    private $emailAddress;

    /**
     * @var FullName
     */
    private $name;

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
        $aUsername,
        FullName $aName,
        EmailAddress $anEmailAddress
    ) {
        $this->emailAddress = $anEmailAddress;
        $this->name = $aName;
        $this->occurredOn = new DateTimeImmutable();
        $this->tenantId = $aTenantId;
        $this->username = $aUsername;
    }

    public function emailAddress()
    {
        return $this->emailAddress;
    }

    public function name()
    {
        return $this->name;
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
