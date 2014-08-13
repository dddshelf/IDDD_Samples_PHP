<?php

namespace SaasOvation\IdentityAccess\Test\Domain\Model\Identity;

use SaasOvation\IdentityAccess\Domain\Model\DomainRegistry;
use SaasOvation\IdentityAccess\Test\Domain\Model\IdentityAccessTest;

class GroupRepositoryTest extends IdentityAccessTest
{
    public function testRemoveGroupReferencedUser()
    {
        $tenant = $this->tenantAggregate();
        $groupA = $tenant->provisionGroup('GroupA', 'A group named GroupA');
        $user = $this->userAggregate();
        DomainRegistry::userRepository()->add($user);
        $groupA->addUser($user);
        DomainRegistry::groupRepository()->add($groupA);

        $this->assertEquals($groupA->groupMembers()->count(), 1);
        $this->assertTrue($groupA->isMember($user, DomainRegistry::groupMemberService()));
        DomainRegistry::userRepository()->remove($user);
        $this->entityManager()->flush();
        $this->entityManager()->detach($groupA);
        $reGrouped = DomainRegistry::groupRepository()->groupNamed($tenant->tenantId(), 'GroupA');
        $this->assertEquals('GroupA', $reGrouped->name());
        $this->assertEquals(1, $reGrouped->groupMembers()->count());
        $this->assertFalse($reGrouped->isMember($user, DomainRegistry::groupMemberService()));
    }

    public function testRepositoryRemoveGroup()
    {
        $tenant = $this->tenantAggregate();
        $groupA = $tenant->provisionGroup('GroupA', 'A group named GroupA');
        DomainRegistry::groupRepository()->add($groupA);
        $notNullGroup = DomainRegistry::groupRepository()->groupNamed($tenant->tenantId(), 'GroupA');
        $this->assertNotNull($notNullGroup);
        DomainRegistry::groupRepository()->remove($groupA);
        $nullGroup = DomainRegistry::groupRepository()->groupNamed($tenant->tenantId(), 'GroupA');
        $this->assertNull($nullGroup);
    }
}
