<?php

namespace SaasOvation\IdentityAccess\Test\Domain\Model\Access;

use SaasOvation\IdentityAccess\Domain\Model\DomainRegistry;
use SaasOvation\IdentityAccess\Test\Domain\Model\IdentityAccessTest;

class AuthorizationServiceTest extends IdentityAccessTest
{
    public function testUserInRoleAuthorization()
    {
        $tenant = $this->tenantAggregate();
        $user = $this->userAggregate();
        DomainRegistry::userRepository()->add($user);
        $managerRole = $tenant->provisionRole('Manager', 'A manager role.', true);

        $managerRole->assignUser($user);

        DomainRegistry::roleRepository()->add($managerRole);

        $authorized = DomainRegistry::authorizationService()->isUserInRole($user, 'Manager');

        $this->assertTrue($authorized);

        $authorized = DomainRegistry::authorizationService()->isUserInRole($user, 'Director');

        $this->assertFalse($authorized);
    }

    public function testUsernameInRoleAuthorization()
    {
        $tenant = $this->tenantAggregate();
        $user = $this->userAggregate();
        DomainRegistry::userRepository()->add($user);
        $managerRole = $tenant->provisionRole('Manager', 'A manager role.', true);

        $managerRole->assignUser($user);

        DomainRegistry::roleRepository()->add($managerRole);

        $authorized = DomainRegistry::authorizationService()->isUserWithTenantInRole($tenant->tenantId(), $user->username(), 'Manager');

        $this->assertTrue($authorized);

        $authorized = DomainRegistry::authorizationService()->isUserWithTenantInRole($tenant->tenantId(), $user->username(), 'Director');

        $this->assertFalse($authorized);
    }
}
