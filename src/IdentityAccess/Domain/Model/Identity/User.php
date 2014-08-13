<?php

namespace SaasOvation\IdentityAccess\Domain\Model\Identity;

use SaasOvation\Common\Domain\Model\ConcurrencySafeEntity;
use SaasOvation\Common\Domain\Model\DomainEventPublisher;
use SaasOvation\Identityaccess\Domain\Model\DomainRegistry;

class User extends ConcurrencySafeEntity
{
    /**
     * @var Enablement
     */
    private $enablement;

    /**
     * @var string
     */
    private $password;

    /**
     * @var Person
     */
    private $person;

    /**
     * @var TenantId
     */
    private $tenantId;

    /**
     * @var string
     */
    private $username;

    public function changePassword($aCurrentPassword, $aChangedPassword)
    {
        $this->assertArgumentNotEmpty(
            $aCurrentPassword,
            'Current and new password must be provided.'
        );
        
        $this->assertArgumentEquals(
            $this->password(),
            $this->asEncryptedValue($aCurrentPassword),
            'Current password not confirmed.'
        );
        
        $this->protectPassword($aCurrentPassword, $aChangedPassword);
        
        DomainEventPublisher::instance()->publish(
            new UserPasswordChanged(
                $this->tenantId(),
                $this->username()
            )
        );
    }

    public function changePersonalContactInformation(ContactInformation $aContactInformation)
    {
        $this->person()->changeContactInformation($aContactInformation);
    }

    public function changePersonalName(FullName $aPersonalName)
    {
        $this->person()->changeName($aPersonalName);
    }

    public function defineEnablement(Enablement $anEnablement)
    {
        $this->setEnablement($anEnablement);

        DomainEventPublisher::instance()->publish(
            new UserEnablementChanged(
                $this->tenantId(),
                $this->username(),
                $this->enablement()
            )
        );
    }

    public function isEnabled()
    {
        return $this->enablement()->isEnablementEnabled();
    }

    public function person()
    {
        return $this->person;
    }

    public function tenantId()
    {
        return $this->tenantId;
    }

    public function userDescriptor()
    {
        return new UserDescriptor(
            $this->tenantId(),
            $this->username(),
            $this->person()->emailAddress()->address()
        );
    }

    public function username()
    {
        return $this->username;
    }

    public function equals($anObject)
    {
        $equalObjects = false;

        if (null !== $anObject && get_class($this) === get_class($anObject)) {
            $equalObjects =
                $this->tenantId()->equals($anObject->tenantId()) &&
                $this->username()->equals($anObject->username());
        }

        return $equalObjects;
    }

    public function __toString()
    {
        return 'User [tenantId=' . $this->tenantId . ', username=' . $this->username . ', person=' . $this->person . ', enablement=' . $this->enablement . ']';
    }

    public function __construct(
        TenantId $aTenantId,
        $aUsername,
        $aPassword,
        Enablement $anEnablement,
        Person $aPerson
    ) {
        $this->setEnablement($anEnablement);
        $this->setPerson($aPerson);
        $this->setTenantId($aTenantId);
        $this->setUsername($aUsername);

        $this->protectPassword('', $aPassword);

        $aPerson->internalOnlySetUser($this);

        DomainEventPublisher::instance()->publish(
            new UserRegistered(
                $this->tenantId(),
                $aUsername,
                $aPerson->name(),
                $aPerson->contactInformation()->emailAddress()
            )
        );
    }

    protected function asEncryptedValue($aPlainTextPassword)
    {
        return DomainRegistry::encryptionService()->encryptedValue($aPlainTextPassword);
    }

    protected function assertPasswordsNotSame($aCurrentPassword, $aChangedPassword)
    {
        $this->assertArgumentNotEquals(
            $aCurrentPassword,
            $aChangedPassword,
            'The password is unchanged.'
        );
    }

    protected function assertPasswordNotWeak($aPlainTextPassword)
    {
        $this->assertArgumentFalse(
            DomainRegistry::passwordService()->isWeak($aPlainTextPassword),
            'The password must be stronger.'
        );
    }

    protected function assertUsernamePasswordNotSame($aPlainTextPassword)
    {
        $this->assertArgumentNotEquals(
            $this->username(),
            $aPlainTextPassword,
            'The username and password must not be the same.'
        );
    }

    public function enablement()
    {
        return $this->enablement;
    }

    protected function setEnablement(Enablement $anEnablement)
    {
        $this->assertArgumentNotNull($anEnablement, 'The enablement is required.');

        $this->enablement = $anEnablement;
    }

    public function internalAccessOnlyEncryptedPassword()
    {
        return $this->password();
    }

    public function password()
    {
        return $this->password;
    }

    protected function setPassword($aPassword)
    {
        $this->password = $aPassword;
    }

    protected function setPerson(Person $aPerson)
    {
        $this->assertArgumentNotNull($aPerson, 'The person is required.');

        $this->person = $aPerson;
    }

    protected function protectPassword($aCurrentPassword, $aChangedPassword)
    {
        $this->assertPasswordsNotSame($aCurrentPassword, $aChangedPassword);

        $this->assertPasswordNotWeak($aChangedPassword);

        $this->assertUsernamePasswordNotSame($aChangedPassword);

        $this->setPassword($this->asEncryptedValue($aChangedPassword));
    }

    protected function setTenantId(TenantId $aTenantId)
    {
        $this->assertArgumentNotNull($aTenantId, 'The tenantId is required.');

        $this->tenantId = $aTenantId;
    }

    public function toGroupMember()
    {
        $groupMember = new GroupMember(
            $this->tenantId(),
            $this->username(),
            new GroupMemberType\User()
        );

        return $groupMember;
    }

    protected function setUsername($aUsername)
    {
        $this->assertArgumentNotEmpty($aUsername, 'The username is required.');
        $this->assertArgumentLength($aUsername, 3, 250, 'The username must be 3 to 250 characters.');

        $this->username = $aUsername;
    }
}
