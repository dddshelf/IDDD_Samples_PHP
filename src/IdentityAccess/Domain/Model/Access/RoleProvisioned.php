<?php

namespace SaasOvation\IdentityAccess\Domain\Model\Access;

use DateTimeImmutable;
use SaasOvation\Common\Domain\Model\DomainEvent;
use SaasOvation\Common\Domain\Model\ImplementsDomainEvent;
use SaasOvation\IdentityAccess\Domain\Model\Identity\TenantId;

class RoleProvisioned implements DomainEvent
{
    use ImplementsDomainEvent;

    /**
     * @var string
     */
    private $name;

    /**
     * @var TenantId
     */
    private $tenantId;

    public function __construct(TenantId $aTenantId, $aName)
    {
        $this->name = $aName;
        $this->occurredOn = new DateTimeImmutable();
        $this->tenantId = $aTenantId;
    }

    public function name()
    {
        return $this->name;
    }

    public function tenantId()
    {
        return $this->tenantId;
    }
}
