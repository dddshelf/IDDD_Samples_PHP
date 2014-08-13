<?php

namespace SaasOvation\IdentityAccess\Test\Domain\Model\Identity;

use SaasOvation\IdentityAccess\Domain\Model\DomainRegistry;
use SaasOvation\IdentityAccess\Test\Domain\Model\IdentityAccessTest;

class AuthenticationServiceTest extends IdentityAccessTest
{
    public function testAuthenticationSuccess()
    {
        $user = $this->userAggregate();

        DomainRegistry::userRepository()->add($user);

        $userDescriptor = DomainRegistry::authenticationService()->authenticate(
            $user->tenantId(),
            $user->username(),
            self::$FIXTURE_PASSWORD
        );

        $this->assertNotNull($userDescriptor);
        $this->assertFalse($userDescriptor->isNullDescriptor());
        $this->assertEquals($userDescriptor->tenantId(), $user->tenantId());
        $this->assertEquals($userDescriptor->username(), $user->username());
        $this->assertEquals($userDescriptor->emailAddress(), $user->person()->emailAddress()->address());
    }

    public function testAuthenticationTenantFailure()
    {
        $user = $this->userAggregate();

        DomainRegistry::userRepository()->add($user);

        $userDescriptor = DomainRegistry::authenticationService()->authenticate(
            DomainRegistry::tenantRepository()->nextIdentity(),
            $user->username(),
            self::$FIXTURE_PASSWORD
        );

        $this->assertNotNull($userDescriptor);
        $this->assertTrue($userDescriptor->isNullDescriptor());
    }

    public function testAuthenticationUsernameFailure()
    {
        $user = $this->userAggregate();

        DomainRegistry::userRepository()->add($user);

        $userDescriptor = DomainRegistry::authenticationService()->authenticate(
            $user->tenantId(),
            self::$FIXTURE_USERNAME2,
            $user->password()
        );

        $this->assertNotNull($userDescriptor);
        $this->assertTrue($userDescriptor->isNullDescriptor());
    }

    public function testAuthenticationPasswordFailure()
    {
        $user = $this->userAggregate();

        DomainRegistry::userRepository()->add($user);

        $userDescriptor = DomainRegistry::authenticationService()->authenticate(
            $user->tenantId(),
            $user->username(),
            self::$FIXTURE_PASSWORD . '-'
        );

        $this->assertNotNull($userDescriptor);
        $this->assertTrue($userDescriptor->isNullDescriptor());
    }
}
