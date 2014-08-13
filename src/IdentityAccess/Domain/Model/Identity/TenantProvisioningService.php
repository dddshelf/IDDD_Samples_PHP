<?php

namespace SaasOvation\IdentityAccess\Domain\Model\Identity;

use Exception;
use LogicException;
use SaasOvation\Common\Domain\Model\DomainEventPublisher;
use SaasOvation\IdentityAccess\Domain\Model\DomainRegistry;
use SaasOvation\IdentityAccess\Domain\Model\Access\Role;
use SaasOvation\IdentityAccess\Domain\Model\Access\RoleRepository;

class TenantProvisioningService
{
    /**
     * @var RoleRepository
     */
    private $roleRepository;

    /**
     * @var TenantRepository
     */
    private $tenantRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(
        TenantRepository $aTenantRepository,
        UserRepository $aUserRepository,
        RoleRepository $aRoleRepository
    ) {
        $this->roleRepository = $aRoleRepository;
        $this->tenantRepository = $aTenantRepository;
        $this->userRepository = $aUserRepository;
    }

    public function provisionTenant(
        $aTenantName,
        $aTenantDescription,
        FullName $anAdministorName,
        EmailAddress $anEmailAddress,
        PostalAddress $aPostalAddress,
        Telephone $aPrimaryTelephone,
        Telephone $aSecondaryTelephone
    ) {
        try {
            $tenant = new Tenant(
                $this->tenantRepository()->nextIdentity(),
                $aTenantName,
                $aTenantDescription,
                true
            ); // must be active to register admin

            $this->tenantRepository()->add($tenant);

            $this->registerAdministratorFor(
                $tenant,
                $anAdministorName,
                $anEmailAddress,
                $aPostalAddress,
                $aPrimaryTelephone,
                $aSecondaryTelephone
            );

            DomainEventPublisher::instance()->publish(
                new TenantProvisioned($tenant->tenantId())
            );

            return $tenant;

        } catch (Exception $t) {
            throw new LogicException(
                'Cannot provision tenant because: ' . $t->getMessage(),
                $t->getCode(),
                $t
            );
        }
    }

    private function registerAdministratorFor(
        Tenant $aTenant,
        FullName $anAdministorName,
        EmailAddress $anEmailAddress,
        PostalAddress $aPostalAddress,
        Telephone $aPrimaryTelephone,
        Telephone $aSecondaryTelephone
    ) {
        $invitation = $aTenant->offerRegistrationInvitation('init')->openEnded();

        $strongPassword = DomainRegistry::passwordService()->generateStrongPassword();

        $admin = $aTenant->registerUser(
            $invitation->invitationId(),
            'admin',
            $strongPassword,
            Enablement::indefiniteEnablement(),
            new Person(
                $aTenant->tenantId(),
                $anAdministorName,
                new ContactInformation(
                    $anEmailAddress,
                    $aPostalAddress,
                    $aPrimaryTelephone,
                    $aSecondaryTelephone
                )
            )
        );

        $aTenant->withdrawInvitation($invitation->invitationId());

        $this->userRepository()->add($admin);

        $adminRole = $aTenant->provisionRole(
            'Administrator',
            'Default ' . $aTenant->name() . ' administrator.'
        );

        $adminRole->assignUser($admin);

        $this->roleRepository()->add($adminRole);

        DomainEventPublisher::instance()->publish(
            new TenantAdministratorRegistered(
                $aTenant->tenantId(),
                $aTenant->name(),
                $anAdministorName,
                $anEmailAddress,
                $admin->username(),
                $strongPassword
            )
        );
    }

    private function roleRepository()
    {
        return $this->roleRepository;
    }

    private function tenantRepository()
    {
        return $this->tenantRepository;
    }

    private function userRepository()
    {
        return $this->userRepository;
    }
}
