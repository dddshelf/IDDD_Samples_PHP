<?php

namespace SaasOvation\IdentityAccess\Domain\Model\Identity;

use SaasOvation\Common\AssertionConcern;

final class UserDescriptor extends AssertionConcern
{
    /**
     * @var string
     */
    private $emailAddress;

    /**
     * @var TenantId
     */
    private $tenantId;

    /**
     * @var string
     */
    private $username;

    public static function nullDescriptorInstance()
    {
        return new UserDescriptor(
            new TenantId('null'),
            null,
            null
        );
    }

    public function __construct(TenantId $aTenantId, $aUsername, $anEmailAddress)
    {
        if (null !== $anEmailAddress) {
            $this->setEmailAddress($anEmailAddress);
        }

        $this->setTenantId($aTenantId);

        if (null !== $aUsername) {
            $this->setUsername($aUsername);
        }
    }

    public function emailAddress()
    {
        return $this->emailAddress;
    }

    public function isNullDescriptor()
    {
        return null === $this->emailAddress() || null === $this->tenantId()->id() || null === $this->username();
    }

    public function tenantId()
    {
        return $this->tenantId;
    }

    public function username()
    {
        return $this->username;
    }

    public function equals($anObject)
    {
        $equalObjects = false;

        if (null !== $anObject && get_class($this) == get_class($anObject)) {
            $equalObjects =
                   $this->emailAddress()->equals($anObject->emailAddress())
                && $this->tenantId()->equals($anObject->tenantId())
                && $this->username()->equals($anObject->username());
        }

        return $equalObjects;
    }

    public function __toString()
    {
        return 'UserDescriptor [emailAddress=' . $this->emailAddress . ', tenantId=' . $this->tenantId . ', username=' . $this->username . ']';
    }

    private function setEmailAddress($anEmailAddress)
    {
        $this->assertArgumentNotEmpty($anEmailAddress, 'Email address must be provided.');

        $this->emailAddress = $anEmailAddress;
    }

    private function setTenantId(TenantId $aTenantId)
    {
        $this->assertArgumentNotNull($aTenantId, 'TenantId must not be set as null.');

        $this->tenantId = $aTenantId;
    }

    private function setUsername($aUsername)
    {
        $this->assertArgumentNotEmpty($aUsername, 'Username must not be set as null.');

        $this->username = $aUsername;
    }
}
