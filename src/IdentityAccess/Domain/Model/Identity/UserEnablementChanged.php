<?php

namespace SaasOvation\IdentityAccess\Domain\Model\Identity;

use DateTimeImmutable;
use SaasOvation\Common\Domain\Model\DomainEvent;
use SaasOvation\Common\Domain\Model\ImplementsDomainEvent;

class UserEnablementChanged implements DomainEvent
{
    use ImplementsDomainEvent;
    
    /**
     * @var Enablement
     */
    private $enablement;

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
        Enablement $anEnablement
    ) {
        $this->enablement = $anEnablement;
        $this->occurredOn = new DateTimeImmutable();
        $this->tenantId = $aTenantId;
        $this->username = $aUsername;
    }

    public function enablement()
    {
        return $this->enablement;
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
