<?php

namespace SaasOvation\IdentityAccess\Domain\Model\Identity;

use DateTimeImmutable;
use SaasOvation\Common\Domain\Model\DomainEvent;
use SaasOvation\Common\Domain\Model\ImplementsDomainEvent;

class TenantAdministratorRegistered implements DomainEvent
{
    use ImplementsDomainEvent;

    /**
     * @var FullName
     */
    private $administratorName;

    /**
     * @var EmailAddress
     */
    private $emailAddress;

    /**
     * @var string
     */
    private $temporaryPassword;

    /**
     * @var TenantId
     */
    private $tenantId;

    /**
     * @var string
     */
    private $tenantName;

    /**
     * @var string
     */
    private $username;

    public function __construct(
        TenantId $aTenantId,
        $aTenantName,
        FullName $anAdministratorName,
        EmailAddress $anEmailAddress,
        $aUsername,
        $aTemporaryPassword
    ) {
        $this->administratorName = $anAdministratorName;
        $this->emailAddress = $anEmailAddress;
        $this->occurredOn = new DateTimeImmutable();
        $this->temporaryPassword = $aTemporaryPassword;
        $this->tenantId = $aTenantId;
        $this->tenantName = $aTenantName;
        $this->username = $aUsername;
    }

    public function administratorName()
    {
        return $this->administratorName;
    }

    public function emailAddress()
    {
        return $this->emailAddress;
    }

    public function temporaryPassword()
    {
        return $this->temporaryPassword;
    }

    public function tenantId()
    {
        return $this->tenantId;
    }

    public function getTenantName()
    {
        return $this->tenantName;
    }

    public function username()
    {
        return $this->username;
    }
}
