<?php

namespace SaasOvation\IdentityAccess\Test\Domain\Model\Identity;

use Exception;
use SaasOvation\Common\Domain\Model\DomainEvent;
use SaasOvation\Common\Domain\Model\DomainEventPublisher;
use SaasOvation\Common\Domain\Model\DomainEventSubscriber;
use SaasOvation\IdentityAccess\Domain\Model\DomainRegistry;
use SaasOvation\IdentityAccess\Domain\Model\Identity\Group;
use SaasOvation\IdentityAccess\Domain\Model\Identity\GroupGroupAdded;
use SaasOvation\IdentityAccess\Domain\Model\Identity\GroupGroupRemoved;
use SaasOvation\IdentityAccess\Domain\Model\Identity\GroupUserAdded;
use SaasOvation\IdentityAccess\Domain\Model\Identity\GroupUserRemoved;
use SaasOvation\IdentityAccess\Test\Domain\Model\IdentityAccessTest;
use SaasOvation\IdentityAccess\Domain\Model\Access\Role;

class GroupTest extends IdentityAccessTest
{
    private $groupGroupAddedCount = 0;
    private $groupGroupRemovedCount = 0;
    private $groupUserAddedCount = 0;
    private $groupUserRemovedCount = 0;

    public function incrementGroupAddedCount()
    {
        ++$this->groupGroupAddedCount;
    }

    public function incrementGroupRemovedCount()
    {
        ++$this->groupGroupRemovedCount;
    }

    public function incrementGroupUserAddedCount()
    {
        ++$this->groupUserAddedCount;
    }

    public function incrementGroupUserRemovedCount()
    {
        ++$this->groupUserRemovedCount;
    }

    public function testProvisionGroup()
    {
        $tenant = $this->tenantAggregate();
        $groupA = $tenant->provisionGroup('GroupA', 'A group named GroupA');
        DomainRegistry::groupRepository()->add($groupA);
        $this->assertEquals(1, DomainRegistry::groupRepository()->allGroups($tenant->tenantId())->count());
    }

    public function testAddGroup()
    {
        DomainEventPublisher::instance()->subscribe(new GroupGroupAddedSubscriber($this));

        $tenant = $this->tenantAggregate();
        $groupA = $tenant->provisionGroup('GroupA', 'A group named GroupA');
        DomainRegistry::groupRepository()->add($groupA);
        $groupB = $tenant->provisionGroup('GroupB', 'A group named GroupB');
        DomainRegistry::groupRepository()->add($groupB);
        $groupA->addGroup($groupB, DomainRegistry::groupMemberService());
        $this->assertEquals(1, $groupA->groupMembers()->count());
        $this->assertEquals(0, $groupB->groupMembers()->count());
        $this->assertEquals(1, $this->groupGroupAddedCount);
    }

    public function testAddUser()
    {
        DomainEventPublisher::instance()->subscribe(new GroupUserAddedSubscriber($this));

        $tenant = $this->tenantAggregate();
        $groupA = $tenant->provisionGroup('GroupA', 'A group named GroupA');
        $user = $this->userAggregate();
        DomainRegistry::userRepository()->add($user);
        $groupA->addUser($user);
        DomainRegistry::groupRepository()->add($groupA);
        $this->assertEquals(1, $groupA->groupMembers()->count());
        $this->assertTrue($groupA->isMember($user, DomainRegistry::groupMemberService()));
        $this->assertEquals(1, $this->groupUserAddedCount);
    }

    public function testRemoveGroup()
    {
        DomainEventPublisher::instance()->subscribe(new GroupGroupRemovedSubscriber($this));

        $tenant = $this->tenantAggregate();
        $groupA = $tenant->provisionGroup('GroupA', 'A group named GroupA');
        DomainRegistry::groupRepository()->add($groupA);
        $groupB = $tenant->provisionGroup('GroupB', 'A group named GroupB');
        DomainRegistry::groupRepository()->add($groupB);
        $groupA->addGroup($groupB, DomainRegistry::groupMemberService());

        $this->assertEquals(1, $groupA->groupMembers()->count());
        $groupA->removeGroup($groupB);
        $this->assertEquals(0, $groupA->groupMembers()->count());
        $this->assertEquals(1, $this->groupGroupRemovedCount);
    }

    public function testRemoveUser()
    {
        DomainEventPublisher::instance()->subscribe(new GroupUserRemovedSubscriber($this));

        $tenant = $this->tenantAggregate();
        $groupA = $tenant->provisionGroup('GroupA', 'A group named GroupA');
        $user = $this->userAggregate();
        DomainRegistry::userRepository()->add($user);
        $groupA->addUser($user);
        DomainRegistry::groupRepository()->add($groupA);

        $this->assertEquals(1, $groupA->groupMembers()->count());
        $groupA->removeUser($user);
        $this->assertEquals(0, $groupA->groupMembers()->count());
        $this->assertEquals(1, $this->groupUserRemovedCount);
    }

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

    public function testUserIsMemberOfNestedGroup()
    {
        DomainEventPublisher::instance()->subscribe(new GroupGroupAddedSubscriber($this));

        $tenant = $this->tenantAggregate();
        $groupA = $tenant->provisionGroup('GroupA', 'A group named GroupA');
        DomainRegistry::groupRepository()->add($groupA);
        $groupB = $tenant->provisionGroup('GroupB', 'A group named GroupB');
        DomainRegistry::groupRepository()->add($groupB);
        $groupA->addGroup($groupB, DomainRegistry::groupMemberService());
        $user = $this->userAggregate();
        DomainRegistry::userRepository()->add($user);
        $groupB->addUser($user);

        $this->assertTrue($groupB->isMember($user, DomainRegistry::groupMemberService()));
        $this->assertTrue($groupA->isMember($user, DomainRegistry::groupMemberService()));

        $this->assertEquals(1, $this->groupGroupAddedCount);
    }

    public function testUserIsNotMember()
    {
        $user = $this->userAggregate();
        DomainRegistry::userRepository()->add($user);
        // tests alternate creation via constructor
        $groupA = new Group($user->tenantId(), 'GroupA', 'A group named GroupA');
        DomainRegistry::groupRepository()->add($groupA);
        $groupB = new Group($user->tenantId(), 'GroupB', 'A group named GroupB');
        DomainRegistry::groupRepository()->add($groupB);
        $groupA->addGroup($groupB, DomainRegistry::groupMemberService());

        $this->assertFalse($groupB->isMember($user, DomainRegistry::groupMemberService()));
        $this->assertFalse($groupA->isMember($user, DomainRegistry::groupMemberService()));
    }

    public function testNoRecursiveGroupings()
    {
        DomainEventPublisher::instance()->subscribe(new GroupGroupAddedSubscriber($this));

        $user = $this->userAggregate();
        DomainRegistry::userRepository()->add($user);
        // tests alternate creation via constructor
        $groupA = new Group($user->tenantId(), 'GroupA', 'A group named GroupA');
        DomainRegistry::groupRepository()->add($groupA);
        $groupB = new Group($user->tenantId(), 'GroupB', 'A group named GroupB');
        DomainRegistry::groupRepository()->add($groupB);
        $groupC = new Group($user->tenantId(), 'GroupC', 'A group named GroupC');
        DomainRegistry::groupRepository()->add($groupC);
        $groupA->addGroup($groupB, DomainRegistry::groupMemberService());
        $groupB->addGroup($groupC, DomainRegistry::groupMemberService());

        $failed = false;

        try {
            $groupC->addGroup($groupA, DomainRegistry::groupMemberService());
        } catch (Exception $t) {
            $failed = true;
        }

        $this->assertTrue($failed);

        $this->assertEquals(2, $this->groupGroupAddedCount);
    }

    public function testNoRoleInternalGroupsInFindAllGroups()
    {
        $tenant = $this->tenantAggregate();
        $groupA = $tenant->provisionGroup('GroupA', 'A group named GroupA');
        DomainRegistry::groupRepository()->add($groupA);

        $roleA = $tenant->provisionRole('RoleA', 'A role of A.');
        DomainRegistry::roleRepository()->add($roleA);
        $roleB = $tenant->provisionRole('RoleB', 'A role of B.');
        DomainRegistry::roleRepository()->add($roleB);
        $roleC = $tenant->provisionRole('RoleC', 'A role of C.');
        DomainRegistry::roleRepository()->add($roleC);

        $groups = DomainRegistry::groupRepository()->allGroups($tenant->tenantId());

        $this->assertEquals(1, $groups->count());
    }
}

class GroupGroupAddedSubscriber implements DomainEventSubscriber
{
    private $test;

    public function __construct(GroupTest $test)
    {
        $this->test = $test;
    }

    public function handleEvent(DomainEvent $aDomainEvent)
    {
        $this->test->incrementGroupAddedCount();
    }

    public function subscribedToEventType()
    {
        return GroupGroupAdded::class;
    }
}

class GroupUserAddedSubscriber implements DomainEventSubscriber
{
    private $test;

    public function __construct(GroupTest $test)
    {
        $this->test = $test;
    }

    public function handleEvent(DomainEvent $aDomainEvent)
    {
        $this->test->incrementGroupUserAddedCount();
    }

    public function subscribedToEventType()
    {
        return GroupUserAdded::class;
    }
}

class GroupGroupRemovedSubscriber implements DomainEventSubscriber
{
    private $test;

    public function __construct(GroupTest $test)
    {
        $this->test = $test;
    }

    public function handleEvent(DomainEvent $aDomainEvent)
    {
        $this->test->incrementGroupRemovedCount();
    }

    public function subscribedToEventType()
    {
        return GroupGroupRemoved::class;
    }
}

class GroupUserRemovedSubscriber implements DomainEventSubscriber
{
    private $test;

    public function __construct(GroupTest $test)
    {
        $this->test = $test;
    }

    public function handleEvent(DomainEvent $aDomainEvent)
    {
        $this->test->incrementGroupUserRemovedCount();
    }

    public function subscribedToEventType()
    {
        return GroupUserRemoved::class;
    }
}