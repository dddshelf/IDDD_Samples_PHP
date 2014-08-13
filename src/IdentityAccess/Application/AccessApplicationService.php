<?php

namespace SaasOvation\IdentityAccess\Application;

use SaasOvation\IdentityAccess\Application\Command\AssignUserToRoleCommand;
use SaasOvation\IdentityAccess\Application\Command\ProvisionRoleCommand;
use SaasOvation\IdentityAccess\Domain\Model\Access\RoleRepository;
use SaasOvation\IdentityAccess\Domain\Model\Identity\GroupMemberService;
use SaasOvation\IdentityAccess\Domain\Model\Identity\GroupRepository;
use SaasOvation\IdentityAccess\Domain\Model\Identity\TenantId;
use SaasOvation\IdentityAccess\Domain\Model\Identity\TenantRepository;
use SaasOvation\IdentityAccess\Domain\Model\Identity\UserRepository;

class AccessApplicationService
{
    /**
     * @var GroupRepository
     */
    private $groupRepository;

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

    public function __construct(GroupRepository $groupRepository, RoleRepository $roleRepository, TenantRepository $tenantRepository, UserRepository $userRepository)
    {
        $this->groupRepository = $groupRepository;
        $this->roleRepository = $roleRepository;
        $this->tenantRepository = $tenantRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @Transactional
     */
    public function assignUserToRole(AssignUserToRoleCommand $aCommand)
    {
        $tenantId = new TenantId($aCommand->getTenantId());

        $user = $this->userRepository()->userWithUsername(
            $tenantId,
            $aCommand->getUsername()
        );

        if (null !== $user) {
            $role = $this->roleRepository()->roleNamed(
                $tenantId,
                $aCommand->getRoleName()
            );

            if (null !== $role) {
                $role->assignUser($user);
            }
        }
    }

    /**
     * @Transactional(readOnly=true)
     */
    public function isUserInRole($aTenantId, $aUsername, $aRoleName)
    {
        $user = $this->userInRole($aTenantId, $aUsername, $aRoleName);

        return null !== $user;
    }

    /**
     * @Transactional
     */
    public function provisionRole(ProvisionRoleCommand $aCommand)
    {
        $tenantId = new TenantId($aCommand->getTenantId());

        $tenant = $this->tenantRepository()->tenantOfId($tenantId);

        $role = $tenant->provisionRole(
            $aCommand->getRoleName(),
            $aCommand->getDescription(),
            $aCommand->isSupportsNesting()
        );

        $this->roleRepository()->add($role);
    }

    /**
     * @Transactional(readOnly=true)
     */
    public function userInRole($aTenantId, $aUsername, $aRoleName)
    {
        $userInRole = null;

        $tenantId = new TenantId($aTenantId);

        $user = $this->userRepository()->userWithUsername(
            $tenantId,
            $aUsername
        );

        if (null !== $user) {
            $role = $this->roleRepository()->roleNamed($tenantId, $aRoleName);

            if (null !== $role) {
                $groupMemberService = new GroupMemberService(
                    $this->userRepository(),
                    $this->groupRepository()
                );

                if ($role->isInRole($user, $groupMemberService)) {
                    $userInRole = $user;
                }
            }
        }

        return $userInRole;
    }

    private function groupRepository()
    {
        return $this->groupRepository;
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
