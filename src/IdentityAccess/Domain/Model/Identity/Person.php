<?php

namespace SaasOvation\IdentityAccess\Domain\Model\Identity;

use SaasOvation\Common\Domain\Model\ConcurrencySafeEntity;
use SaasOvation\Common\Domain\Model\DomainEventPublisher;

class Person extends ConcurrencySafeEntity
{
    /**
     * @var ContactInformation
     */
    private $contactInformation;

    /**
     * @var FullName
     */
    private $name;

    /**
     * @var TenantId
     */
    private $tenantId;

    /**
     * @var User
     */
    private $user;

    public function __construct(
        TenantId $aTenantId,
        FullName $aName,
        ContactInformation $aContactInformation
    ) {
        $this->setContactInformation($aContactInformation);
        $this->setName($aName);
        $this->setTenantId($aTenantId);
    }

    public function changeContactInformation(ContactInformation $aContactInformation)
    {
        $this->setContactInformation($aContactInformation);

        DomainEventPublisher::instance()->publish(
            new PersonContactInformationChanged(
                $this->tenantId(),
                $this->user()->username(),
                $this->contactInformation()
            )
        );
    }

    public function changeName(FullName $aName)
    {
        $this->setName($aName);

        DomainEventPublisher::instance()->publish(
            new PersonNameChanged(
                $this->tenantId(),
                $this->user()->username(),
                $this->name()
            )
        );
    }

    public function contactInformation()
    {
        return $this->contactInformation;
    }

    public function emailAddress()
    {
        return $this->contactInformation()->emailAddress();
    }

    public function name()
    {
        return $this->name;
    }

    public function equals($anObject)
    {
        $equalObjects = false;

        if ($anObject !== null && get_class($this) === get_class($anObject)) {
            $equalObjects =
                $this->tenantId()->equals($anObject->tenantId()) &&
                $this->user()->username()->equals($anObject->user()->username());
        }

        return $equalObjects;
    }

    public function __toString()
    {
        return 'Person [tenantId=' . $this->tenantId . ', name=' . $this->name . ', contactInformation=' . $this->contactInformation . ']';
    }

    protected function setContactInformation(ContactInformation $aContactInformation)
    {
        $this->assertArgumentNotNull($aContactInformation, 'The person contact information is required.');

        $this->contactInformation = $aContactInformation;
    }

    protected function setName(FullName $aName)
    {
        $this->assertArgumentNotNull($aName, 'The person name is required.');

        $this->name = $aName;
    }

    protected function tenantId()
    {
        return $this->tenantId;
    }

    public function setTenantId(TenantId $aTenantId)
    {
        $this->assertArgumentNotNull($aTenantId, 'The tenantId is required.');

        $this->tenantId = $aTenantId;
    }

    protected function user()
    {
        return $this->user;
    }

    public function internalOnlySetUser(User $aUser)
    {
        $this->user = $aUser;
    }
}
