<?php

namespace SaasOvation\IdentityAccess\Domain\Model\Access;

use Saasovation\Common\AssertionConcern;
use SaasOvation\IdentityAccess\Domain\Model\Identity\GroupMemberService;
use Saasovation\IdentityAccess\Domain\Model\Identity\GroupRepository;
use Saasovation\IdentityAccess\Domain\Model\Identity\TenantId;
use Saasovation\IdentityAccess\Domain\Model\Identity\User;
use Saasovation\IdentityAccess\Domain\Model\Identity\UserRepository;

class AuthorizationService extends AssertionConcern
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
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(
        UserRepository $aUserRepository,
        GroupRepository $aGroupRepository,
        RoleRepository $aRoleRepository
    ) {
        $this->groupRepository = $aGroupRepository;
        $this->roleRepository = $aRoleRepository;
        $this->userRepository = $aUserRepository;
    }

    public function isUserWithTenantInRole(TenantId $aTenantId, $aUsername, $aRoleName)
    {
        $this->assertArgumentNotNull($aTenantId, 'TenantId must not be null.');
        $this->assertArgumentNotEmpty($aUsername, 'Username must not be provided.');
        $this->assertArgumentNotEmpty($aRoleName, 'Role name must not be null.');

        $user = $this->userRepository()->userWithUsername($aTenantId, $aUsername);

        return null === $user ? false : $this->isUserInRole($user, $aRoleName);
    }

    public function isUserInRole(User $aUser, $aRoleName)
    {
        $this->assertArgumentNotNull($aUser, 'User must not be null.');
        $this->assertArgumentNotEmpty($aRoleName, 'Role name must not be null.');

        $authorized = false;

        if ($aUser->isEnabled()) {
            $role = $this->roleRepository()->roleNamed($aUser->tenantId(), $aRoleName);

            if (null !== $role) {
                $groupMemberService = new GroupMemberService(
                    $this->userRepository(),
                    $this->groupRepository()
                );

                $authorized = $role->isInRole($aUser, $groupMemberService);
            }
        }

        return $authorized;
    }

    private function groupRepository()
    {
        return $this->groupRepository;
    }

    private function roleRepository()
    {
        return $this->roleRepository;
    }

    private function userRepository()
    {
        return $this->userRepository;
    }
}
