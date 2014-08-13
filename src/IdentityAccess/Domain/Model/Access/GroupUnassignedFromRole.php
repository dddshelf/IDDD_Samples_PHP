<?php

namespace SaasOvation\IdentityAccess\Domain\Model\Access;

use DateTimeImmutable;
use SaasOvation\Common\Domain\Model\DomainEvent;
use SaasOvation\Common\Domain\Model\ImplementsDomainEvent;
use SaasOvation\IdentityAccess\Domain\Model\Identity\TenantId;

class GroupUnassignedFromRole implements DomainEvent
{
    use ImplementsDomainEvent;

    /**
     * @var string
     */
    private $groupName;

    /**
     * @var string
     */
    private $roleName;

    /**
     * @var TenantId
     */
    private $tenantId;

    public function __construct(TenantId $aTenantId, $aRoleName, $aGroupName)
    {
        $this->groupName = $aGroupName;
        $this->occurredOn = new DateTimeImmutable();
        $this->roleName = $aRoleName;
        $this->tenantId = $aTenantId;
    }

    public function groupName()
    {
        return $this->groupName;
    }

    public function roleName()
    {
        return $this->roleName;
    }

    public function tenantId()
    {
        return $this->tenantId;
    }
}
