<?php

namespace SaasOvation\IdentityAccess\Domain\Model\Identity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Rhumsaa\Uuid\Uuid;
use SaasOvation\Common\Domain\Model\ConcurrencySafeEntity;
use SaasOvation\Common\Domain\Model\DomainEventPublisher;
use SaasOvation\IdentityAccess\Domain\Model\Access\Role;
use SaasOvation\IdentityAccess\Domain\Model\Access\RoleProvisioned;

class Tenant extends ConcurrencySafeEntity
{
    /**
     * @var boolean
     */
    private $active;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $name;

    /**
     * @var Collection
     */
    private $registrationInvitations;

    /**
     * @var TenantId
     */
    private $tenantId;

    public function __construct(TenantId $aTenantId, $aName, $aDescription, $anActive)
    {
        $this->setRegistrationInvitations(new ArrayCollection());

        $this->setActive($anActive);
        $this->setDescription($aDescription);
        $this->setName($aName);
        $this->setTenantId($aTenantId);
    }

    public function activate()
    {
        if (!$this->isActive()) {

            $this->setActive(true);

            DomainEventPublisher::instance()->publish(
                new TenantActivated($this->tenantId())
            );
        }
    }

    public function allAvailableRegistrationInvitations()
    {
        $this->assertStateTrue($this->isActive(), 'Tenant is not active.');

        return $this->allRegistrationInvitationsFor(true);
    }

    public function allUnavailableRegistrationInvitations()
    {
        $this->assertStateTrue($this->isActive(), 'Tenant is not active.');

        return $this->allRegistrationInvitationsFor(false);
    }

    public function deactivate()
    {
        if ($this->isActive()) {

            $this->setActive(false);

            DomainEventPublisher::instance()->publish(
                new TenantDeactivated($this->tenantId())
            );
        }
    }

    public function description()
    {
        return $this->description;
    }

    public function isActive()
    {
        return $this->active;
    }

    public function isRegistrationAvailableThrough($anInvitationIdentifier)
    {
        $this->assertStateTrue($this->isActive(), 'Tenant is not active.');

        $invitation = $this->invitation($anInvitationIdentifier);

        return false === $invitation ? false : $invitation->isAvailable();
    }

    public function name()
    {
        return $this->name;
    }

    public function offerRegistrationInvitation($aDescription)
    {
        $this->assertStateTrue($this->isActive(), 'Tenant is not active.');

        $this->assertStateFalse(
            $this->isRegistrationAvailableThrough($aDescription),
            'Invitation already exists.'
        );

        $invitation = new RegistrationInvitation(
            $this->tenantId(),
            strtoupper(Uuid::uuid4()),
            $aDescription
        );

        $added = $this->registrationInvitations()->add($invitation);

        $this->assertStateTrue($added, 'The invitation should have been added.');

        return $invitation;
    }

    public function provisionGroup($aName, $aDescription)
    {
        $this->assertStateTrue($this->isActive(), 'Tenant is not active.');

        $group = new Group($this->tenantId(), $aName, $aDescription);

        DomainEventPublisher::instance()->publish(
            new GroupProvisioned(
                $this->tenantId(),
                $aName
            )
        );

        return $group;
    }

    public function provisionRole($aName, $aDescription, $aSupportsNesting = false)
    {
        $this->assertStateTrue($this->isActive(), 'Tenant is not active.');

        $role = new Role($this->tenantId(), $aName, $aDescription, $aSupportsNesting);

        DomainEventPublisher::instance()->publish(
            new RoleProvisioned(
                $this->tenantId(),
                $aName
            )
        );

        return $role;
    }

    public function redefineRegistrationInvitationAs($anInvitationIdentifier)
    {
        $this->assertStateTrue($this->isActive(), 'Tenant is not active.');

        $invitation = $this->invitation($anInvitationIdentifier);

        if (null !== $invitation) {
            $invitation->redefineAs()->openEnded();
        }

        return $invitation;
    }

    public function registerUser(
        $anInvitationIdentifier,
        $aUsername,
        $aPassword,
        Enablement $anEnablement,
        Person $aPerson
    ) {
        $this->assertStateTrue($this->isActive(), 'Tenant is not active.');

        $user = null;

        if ($this->isRegistrationAvailableThrough($anInvitationIdentifier)) {

            // ensure same tenant
            $aPerson->setTenantId($this->tenantId());

            $user = new User(
                $this->tenantId(),
                $aUsername,
                $aPassword,
                $anEnablement,
                $aPerson
            );
        }

        return $user;
    }

    public function tenantId()
    {
        return $this->tenantId;
    }

    public function withdrawInvitation($anInvitationIdentifier)
    {
        $invitation = $this->invitation($anInvitationIdentifier);

        if (null !== $invitation) {
            $this->registrationInvitations()->removeElement($invitation);
        }
    }

    public function equals($anObject)
    {
        $equalObjects = false;

        if (null !== $anObject && get_class($this) === get_class($anObject)) {
            $equalObjects =
                $this->tenantId() === $anObject->tenantId() &&
                $this->name() === $anObject->name();
        }

        return $equalObjects;
    }

    public function __toString()
    {
        return 'Tenant [active=' . $this->active . ', description=' . $this->description
        . ', name=' . $this->name . ', tenantId=' . $this->tenantId . ']';
    }

    protected function setActive($anActive)
    {
        $this->active = $anActive;
    }

    protected function allRegistrationInvitationsFor($isAvailable)
    {
        return $this->registrationInvitations()
            ->filter(function(RegistrationInvitation $invitation) use ($isAvailable) {
                    return $invitation->isAvailable() === $isAvailable;
            })
            ->map(function(RegistrationInvitation $invitation) {
                return $invitation->toDescriptor();
            })
        ;
    }

    protected function setDescription($aDescription)
    {
        $this->assertArgumentNotEmpty($aDescription, 'The tenant description is required.');
        $this->assertArgumentLength($aDescription, 1, 100, 'The tenant description must be 100 characters or less.');

        $this->description = $aDescription;
    }

    protected function invitation($anInvitationIdentifier)
    {
        $invitations = $this->registrationInvitations()->filter(function (RegistrationInvitation $invitation) use ($anInvitationIdentifier) {
            return $invitation->isIdentifiedBy($anInvitationIdentifier);
        });

        return $invitations->first();
    }

    protected function setName($aName)
    {
        $this->assertArgumentNotEmpty($aName, 'The tenant name is required.');
        $this->assertArgumentLength($aName, 1, 100, 'The name must be 100 characters or less.');

        $this->name = $aName;
    }

    protected function registrationInvitations()
    {
        return $this->registrationInvitations;
    }

    protected function setRegistrationInvitations(Collection $aRegistrationInvitations)
    {
        $this->registrationInvitations = $aRegistrationInvitations;
    }

    protected function setTenantId(TenantId $aTenantId)
    {
        $this->assertArgumentNotNull($aTenantId, 'TenentId is required.');

        $this->tenantId = $aTenantId;
    }
}
