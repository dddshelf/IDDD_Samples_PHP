<?php

namespace SaasOvation\IdentityAccess\Domain\Model\Identity;

use DateTimeImmutable;
use SaasOvation\Common\Domain\Model\DomainEvent;
use SaasOvation\Common\Domain\Model\ImplementsDomainEvent;

class TenantDeactivated implements DomainEvent
{
    use ImplementsDomainEvent;
    
    /**
     * @var TenantId
     */
    private $tenantId;

    public function __construct(TenantId $aTenantId)
    {
        $this->occurredOn = new DateTimeImmutable();
        $this->tenantId = $aTenantId;
    }

    public function tenantId()
    {
        return $this->tenantId;
    }
}
