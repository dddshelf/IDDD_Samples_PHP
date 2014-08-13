<?php

namespace SaasOvation\IdentityAccess\Domain\Model\Access;

use DateTimeImmutable;
use SaasOvation\Common\Domain\Model\DomainEvent;
use SaasOvation\Common\Domain\Model\ImplementsDomainEvent;
use SaasOvation\IdentityAccess\Domain\Model\Identity\TenantId;

class UserUnassignedFromRole implements DomainEvent
{
    use ImplementsDomainEvent;

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
        $aUsername
    ) {
        $this->occurredOn = new DateTimeImmutable();
        $this->roleName = $aRoleName;
        $this->tenantId = $aTenantId;
        $this->username = $aUsername;
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
