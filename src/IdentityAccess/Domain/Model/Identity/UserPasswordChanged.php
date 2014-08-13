<?php

namespace SaasOvation\IdentityAccess\Domain\Model\Identity;

use DateTimeImmutable;
use SaasOvation\Common\Domain\Model\DomainEvent;
use SaasOvation\Common\Domain\Model\ImplementsDomainEvent;

class UserPasswordChanged implements DomainEvent
{
    use ImplementsDomainEvent;
    
    /**
     * @var TenantId
     */
    private $tenantId;

    /**
     * @var string
     */
    private $username;
    
    public function __construct(TenantId $aTenantId, $aUsername)
    {
        $this->occurredOn = new DateTimeImmutable();
        $this->tenantId = $aTenantId;
        $this->username = $aUsername;
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
