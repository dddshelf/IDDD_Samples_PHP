<?php

namespace SaasOvation\IdentityAccess\Domain\Model\Identity;

use DateTimeImmutable;
use SaasOvation\Common\Domain\Model\DomainEvent;
use SaasOvation\Common\Domain\Model\ImplementsDomainEvent;

class PersonContactInformationChanged implements DomainEvent
{
    use ImplementsDomainEvent;
    
    /**
     * @var ContactInformation
     */
    private $contactInformation;

    /**
     * @var TenantId
     */
    private $tenantId;

    /**
     * @var string
     */
    private $username;
    
    public function __construct(TenantId $aTenantId, $aUsername, ContactInformation $aContactInformation)
    {
        $this->contactInformation = $aContactInformation;
        $this->occurredOn = new DateTimeImmutable();
        $this->tenantId = $aTenantId;
        $this->username = $aUsername;
    }

    public function contactInformation()
    {
        return $this->contactInformation;
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
