<?php

namespace SaasOvation\IdentityAccess\Domain\Model\Identity;

use DateTimeImmutable;
use SaasOvation\Common\Domain\Model\DomainEvent;
use SaasOvation\Common\Domain\Model\ImplementsDomainEvent;

class GroupUserRemoved implements DomainEvent
{
    use ImplementsDomainEvent;

    /**
     * @var string
     */
    private $groupName;

    /**
     * @var TenantId
     */
    private $tenantId;

    /**
     * @var string
     */
    private $username;

    public function __construct(TenantId $aTenantId, $aGroupName, $aUsername)
    {
        $this->groupName = $aGroupName;
        $this->occurredOn = new DateTimeImmutable();
        $this->tenantId = $aTenantId;
        $this->username = $aUsername;
    }

    public function groupName()
    {
        return $this->groupName;
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
