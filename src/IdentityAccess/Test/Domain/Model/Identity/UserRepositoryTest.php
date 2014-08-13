<?php

namespace SaasOvation\IdentityAccess\Test\Domain\Model\Identity;

use SaasOvation\IdentityAccess\Domain\Model\DomainRegistry;
use SaasOvation\IdentityAccess\Test\Domain\Model\IdentityAccessTest;

class UserRepositoryTest extends IdentityAccessTest
{
    public function testAddUser()
    {
        $user = $this->userAggregate();
    
        DomainRegistry::userRepository()->add($user);
    
        $this->assertNotNull(
            DomainRegistry::userRepository()->userWithUsername($user->tenantId(), $user->username())
        );
    }

    public function testFindUserByUsername()
    {
        $user = $this->userAggregate();

        DomainRegistry::userRepository()->add($user);

        $this->assertNotNull(
            DomainRegistry::userRepository()->userWithUsername($user->tenantId(), $user->username())
        );
    }

    public function testRemoveUser()
    {
        $user = $this->userAggregate();

        DomainRegistry::userRepository()->add($user);

        $this->assertNotNull(
            DomainRegistry::userRepository()->userWithUsername($user->tenantId(), $user->username())
        );

        DomainRegistry::userRepository()->remove($user);

        $this->assertNull(
            DomainRegistry::userRepository()->userWithUsername($user->tenantId(), $user->username())
        );
    }

    public function testFindSimilarlyNamedUsers()
    {
        $user = $this->userAggregate();
        DomainRegistry::userRepository()->add($user);

        $user2 = $this->userAggregate2();
        DomainRegistry::userRepository()->add($user2);

        $name = $user->person()->name();

        $users = DomainRegistry::userRepository()->allSimilarlyNamedUsers(
            $user->tenantId(),
            '',
            substr($name->lastName(), 0, 2)
        );

        $this->assertEquals(2, $users->count());
    }

}
