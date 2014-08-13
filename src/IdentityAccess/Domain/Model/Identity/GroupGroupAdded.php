<?php

namespace SaasOvation\IdentityAccess\Domain\Model\Identity;

use DateTimeImmutable;
use SaasOvation\Common\Domain\Model\DomainEvent;
use SaasOvation\Common\Domain\Model\ImplementsDomainEvent;

class GroupGroupAdded implements DomainEvent
{
    use ImplementsDomainEvent;

    /**
     * @var string
     */
    private $groupName;

    /**
     * @var string
     */
    private $nestedGroupName;

    /**
     * @var TenantId
     */
    private $tenantId;

    public function __construct(TenantId $aTenantId, $aGroupName, $aNestedGroupName)
    {
        $this->groupName = $aGroupName;
        $this->nestedGroupName = $aNestedGroupName;
        $this->occurredOn = new DateTimeImmutable();
        $this->tenantId = $aTenantId;
    }

    public function groupName()
    {
        return $this->groupName;
    }

    public function nestedGroupName()
    {
        return $this->nestedGroupName;
    }

    public function tenantId()
    {
        return $this->tenantId;
    }
}
