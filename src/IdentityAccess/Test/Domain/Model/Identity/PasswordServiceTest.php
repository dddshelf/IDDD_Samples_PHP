<?php

namespace SaasOvation\IdentityAccess\Test\Domain\Model\Identity;

use SaasOvation\IdentityAccess\Domain\Model\DomainRegistry;
use SaasOvation\IdentityAccess\Test\Domain\Model\IdentityAccessTest;

class PasswordServiceTest extends IdentityAccessTest
{
    public function testGenerateStrongPassword()
    {
        $password = DomainRegistry::passwordService()->generateStrongPassword();

        $this->assertTrue(DomainRegistry::passwordService()->isStrong($password));
        $this->assertFalse(DomainRegistry::passwordService()->isWeak($password));
    }

    public function testIsStrongPassword()
    {
        $password = 'Th1sShudBStrong.';
        $this->assertTrue(DomainRegistry::passwordService()->isStrong($password));
        $this->assertFalse(DomainRegistry::passwordService()->isVeryStrong($password));
        $this->assertFalse(DomainRegistry::passwordService()->isWeak($password));
    }

    public function testIsVeryStrongPassword()
    {
        $password = 'Th1sSh0uldBV3ryStrong!';
        $this->assertTrue(DomainRegistry::passwordService()->isVeryStrong($password));
        $this->assertTrue(DomainRegistry::passwordService()->isStrong($password));
        $this->assertFalse(DomainRegistry::passwordService()->isWeak($password));
    }

    public function testIsWeakPassword()
    {
        $password = 'Weakness';
        $this->assertFalse(DomainRegistry::passwordService()->isVeryStrong($password));
        $this->assertFalse(DomainRegistry::passwordService()->isStrong($password));
        $this->assertTrue(DomainRegistry::passwordService()->isWeak($password));
    }
}
