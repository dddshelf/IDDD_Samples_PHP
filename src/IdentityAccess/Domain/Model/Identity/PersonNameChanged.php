<?php

namespace SaasOvation\IdentityAccess\Domain\Model\Identity;

use DateTimeImmutable;
use SaasOvation\Common\Domain\Model\DomainEvent;
use SaasOvation\Common\Domain\Model\ImplementsDomainEvent;

class PersonNameChanged implements DomainEvent
{
    use ImplementsDomainEvent;

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

    public function __construct(TenantId $aTenantId, $aUsername, FullName $aName)
    {
        $this->name = $aName;
        $this->occurredOn = new DateTimeImmutable();
        $this->tenantId = $aTenantId;
        $this->username = $aUsername;
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
