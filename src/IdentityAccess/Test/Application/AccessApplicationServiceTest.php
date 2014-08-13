<?php

namespace SaasOvation\IdentityAccess\Test\Application;

use SaasOvation\IdentityAccess\Application\ApplicationServiceRegistry;
use SaasOvation\IdentityAccess\Application\Command\AssignUserToRoleCommand;
use SaasOvation\IdentityAccess\Domain\Model\DomainRegistry;

class AccessApplicationServiceTest extends ApplicationServiceTest
{
    public function testAssignUserToRole()
    {
        $user = $this->userAggregate();
        DomainRegistry::userRepository()->add($user);

        $role = $this->roleAggregate();
        DomainRegistry::roleRepository()->add($role);

        $this->assertFalse(
            $role->isInRole($user, DomainRegistry::groupMemberService())
        );

        ApplicationServiceRegistry::accessApplicationService()->assignUserToRole(
            new AssignUserToRoleCommand(
                $user->tenantId()->id(),
                $user->username(),
                $role->name()
            )
        );

        $this->assertTrue(
            $role->isInRole($user, DomainRegistry::groupMemberService())
        );
    }

    public function testIsUserInRole()
    {
        $user = $this->userAggregate();
        DomainRegistry::userRepository()->add($user);

        $role = $this->roleAggregate();
        DomainRegistry::roleRepository()->add($role);

        $this->assertFalse(
            ApplicationServiceRegistry::accessApplicationService()->isUserInRole(
                $user->tenantId()->id(),
                $user->username(),
                $role->name()
            )
        );

        ApplicationServiceRegistry::accessApplicationService()->assignUserToRole(
            new AssignUserToRoleCommand(
                $user->tenantId()->id(),
                $user->username(),
                $role->name()
            )
        );

        $this->assertTrue(
            ApplicationServiceRegistry::accessApplicationService()->isUserInRole(
                $user->tenantId()->id(),
                $user->username(),
                $role->name()
            )
        );
    }

    public function testUserInRole()
    {
        $user = $this->userAggregate();
        DomainRegistry::userRepository()->add($user);

        $role = $this->roleAggregate();
        DomainRegistry::roleRepository()->add($role);

        $userNotInRole = ApplicationServiceRegistry::accessApplicationService()->userInRole(
            $user->tenantId()->id(),
            $user->username(),
            $role->name()
        );

        $this->assertNull($userNotInRole);

        ApplicationServiceRegistry::accessApplicationService()->assignUserToRole(
            new AssignUserToRoleCommand(
                $user->tenantId()->id(),
                $user->username(),
                $role->name()
            )
        );

        $userInRole = ApplicationServiceRegistry::accessApplicationService()->userInRole(
            $user->tenantId()->id(),
            $user->username(),
            $role->name()
        );

        $this->assertNotNull($userInRole);
    }
}
