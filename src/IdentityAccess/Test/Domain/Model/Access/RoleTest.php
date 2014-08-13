<?php

namespace SaasOvation\IdentityAccess\Test\Domain\Model\Access;

use Exception;
use SaasOvation\Common\Domain\Model\DomainEvent;
use SaasOvation\Common\Domain\Model\DomainEventPublisher;
use SaasOvation\Common\Domain\Model\DomainEventSubscriber;
use SaasOvation\IdentityAccess\Domain\Model\Access\GroupAssignedToRole;
use SaasOvation\IdentityAccess\Domain\Model\Access\GroupUnassignedFromRole;
use SaasOvation\IdentityAccess\Domain\Model\Access\Role;
use SaasOvation\IdentityAccess\Domain\Model\Access\UserAssignedToRole;
use SaasOvation\IdentityAccess\Domain\Model\Access\UserUnassignedFromRole;
use SaasOvation\IdentityAccess\Domain\Model\DomainRegistry;
use SaasOvation\IdentityAccess\Test\Domain\Model\IdentityAccessTest;
use SaasOvation\IdentityAccess\Domain\Model\Identity\Group;
use SaasOvation\IdentityAccess\Domain\Model\Identity\GroupGroupAdded;
use SaasOvation\IdentityAccess\Domain\Model\Identity\GroupGroupRemoved;
use SaasOvation\IdentityAccess\Domain\Model\Identity\GroupUserAdded;
use SaasOvation\IdentityAccess\Domain\Model\Identity\GroupUserRemoved;

class RoleTest extends IdentityAccessTest
{
    /**
     * @var int
     */
    private $groupSomethingAddedCount = 0;

    /**
     * @var int
     */
    private $groupSomethingRemovedCount = 0;

    /**
     * @var int
     */
    private $roleSomethingAssignedCount = 0;

    /**
     * @var int
     */
    private $roleSomethingUnassignedCount = 0;

    public function testProvisionRole()
    {
        $tenant = $this->tenantAggregate();
        $role = $tenant->provisionRole('Manager', 'A manager role.');
        DomainRegistry::roleRepository()->add($role);
        $this->assertEquals(1, DomainRegistry::roleRepository()->allRoles($tenant->tenantId())->count());
    }

    public function testRoleUniqueness()
    {
        $tenant = $this->tenantAggregate();
        $role1 = $tenant->provisionRole('Manager', 'A manager role.');
        DomainRegistry::roleRepository()->add($role1);

        $nonUnique = false;

        try {
            $role2 = $tenant->provisionRole('Manager', 'A manager role.');
            DomainRegistry::roleRepository()->add($role2);

            $this->fail('Should have thrown exception for nonuniqueness.');

        } catch (Exception $e) {
            $nonUnique = true;
        }

        $this->assertTrue($nonUnique);
    }

    public function testUserIsInRole()
    {
        $tenant = $this->tenantAggregate();
        $user = $this->userAggregate();
        DomainRegistry::userRepository()->add($user);
        $managerRole = $tenant->provisionRole('Manager', 'A manager role.', true);
        $group = new Group($user->tenantId(), 'Managers', 'A group of managers.');
        DomainRegistry::groupRepository()->add($group);
        $managerRole->assignGroup($group, DomainRegistry::groupMemberService());
        DomainRegistry::roleRepository()->add($managerRole);
        $group->addUser($user);

        $this->assertTrue($group->isMember($user, DomainRegistry::groupMemberService()));
        $this->assertTrue($managerRole->isInRole($user, DomainRegistry::groupMemberService()));
    }

    public function testUserIsNotInRole()
    {
        $tenant = $this->tenantAggregate();
        $user = $this->userAggregate();
        DomainRegistry::userRepository()->add($user);
        $managerRole = $tenant->provisionRole('Manager', 'A manager role.', true);
        $group = $tenant->provisionGroup('Managers', 'A group of managers.');
        DomainRegistry::groupRepository()->add($group);
        $managerRole->assignGroup($group, DomainRegistry::groupMemberService());
        DomainRegistry::roleRepository()->add($managerRole);
        $accountantRole = new Role($user->tenantId(), 'Accountant', 'An accountant role.');
        DomainRegistry::roleRepository()->add($accountantRole);

        $this->assertFalse($managerRole->isInRole($user, DomainRegistry::groupMemberService()));
        $this->assertFalse($accountantRole->isInRole($user, DomainRegistry::groupMemberService()));
    }

    public function testNoRoleInternalGroupsInFindGroupByName()
    {
        $tenant = $this->tenantAggregate();
        $roleA = $tenant->provisionRole('RoleA', 'A role of A.');
        DomainRegistry::roleRepository()->add($roleA);

        $error = false;

        try {

            DomainRegistry::groupRepository()->groupNamed(
                $tenant->tenantId(),
                $roleA->group()->name()
            );

            $this->fail('Should have thrown exception for invalid group name.');

        } catch (Exception $e) {
            $error = true;
        }

        $this->assertTrue($error);
    }

    public function testInternalGroupAddedEventsNotPublished()
    {
        DomainEventPublisher::instance()->subscribe(new GroupAssignedToRoleSubscriber($this));
        DomainEventPublisher::instance()->subscribe(new GroupGroupAddedSubscriber($this));
        DomainEventPublisher::instance()->subscribe(new UserAssignedToRoleSubscriber($this));
        DomainEventPublisher::instance()->subscribe(new GroupUserAddedSubscriber($this));

        $tenant = $this->tenantAggregate();
        $user = $this->userAggregate();
        DomainRegistry::userRepository()->add($user);
        $managerRole = $tenant->provisionRole('Manager', 'A manager role.', true);
        $group = new Group($user->tenantId(), 'Managers', 'A group of managers.');
        DomainRegistry::groupRepository()->add($group);
        $managerRole->assignGroup($group, DomainRegistry::groupMemberService());
        $managerRole->assignUser($user);
        DomainRegistry::roleRepository()->add($managerRole);
        $group->addUser($user); // legal add

        $this->assertEquals(2, $this->roleSomethingAssignedCount);
        $this->assertEquals(1, $this->groupSomethingAddedCount);
    }

    public function testInternalGroupRemovedEventsNotPublished()
    {
        DomainEventPublisher::instance()->subscribe(new GroupUnassignedFromRoleSubscriber($this));
        DomainEventPublisher::instance()->subscribe(new GroupGroupRemovedSubscriber($this));
        DomainEventPublisher::instance()->subscribe(new UserUnassignedFromRoleSubscriber($this));
        DomainEventPublisher::instance()->subscribe(new GroupUserRemovedSubscriber($this));

        $tenant = $this->tenantAggregate();
        $user = $this->userAggregate();
        DomainRegistry::userRepository()->add($user);
        $managerRole = $tenant->provisionRole('Manager', 'A manager role.', true);
        $group = new Group($user->tenantId(), 'Managers', 'A group of managers.');
        DomainRegistry::groupRepository()->add($group);
        $managerRole->assignUser($user);
        $managerRole->assignGroup($group, DomainRegistry::groupMemberService());
        DomainRegistry::roleRepository()->add($managerRole);

        $managerRole->unassignUser($user);
        $managerRole->unassignGroup($group);

        $this->assertEquals(2, $this->roleSomethingUnassignedCount);
        $this->assertEquals(0, $this->groupSomethingRemovedCount);
    }

    public function incrementRoleSomethingUnassignedCount()
    {
        ++$this->roleSomethingUnassignedCount;
    }

    public function incrementGroupSomethingRemovedCount()
    {
        ++$this->groupSomethingRemovedCount;
    }

    public function incrementRoleSomethingAssignedCount()
    {
        ++$this->roleSomethingAssignedCount;
    }

    public function incrementGroupSomethingAddedCount()
    {
        ++$this->groupSomethingAddedCount;
    }
}

class GroupAssignedToRoleSubscriber implements DomainEventSubscriber
{
    private $test;

    public function __construct(RoleTest $test)
    {
        $this->test = $test;
    }

    public function handleEvent(DomainEvent $aDomainEvent)
    {
        $this->test->incrementRoleSomethingAssignedCount();
    }

    public function subscribedToEventType()
    {
        return GroupAssignedToRole::class;
    }
}

class GroupUnassignedFromRoleSubscriber implements DomainEventSubscriber
{
    private $test;

    public function __construct(RoleTest $test)
    {
        $this->test = $test;
    }

    public function handleEvent(DomainEvent $aDomainEvent)
    {
        $this->test->incrementRoleSomethingUnassignedCount();
    }

    public function subscribedToEventType()
    {
        return GroupUnassignedFromRole::class;
    }
}

class GroupGroupAddedSubscriber implements DomainEventSubscriber
{
    private $test;

    public function __construct(RoleTest $test)
    {
        $this->test = $test;
    }

    public function handleEvent(DomainEvent $aDomainEvent)
    {
        $this->test->incrementGroupSomethingAddedCount();
    }

    public function subscribedToEventType()
    {
        return GroupGroupAdded::class;
    }
}

class GroupGroupRemovedSubscriber implements DomainEventSubscriber
{
    private $test;

    public function __construct(RoleTest $test)
    {
        $this->test = $test;
    }

    public function handleEvent(DomainEvent $aDomainEvent)
    {
        $this->test->incrementGroupSomethingRemovedCount();
    }

    public function subscribedToEventType()
    {
        return GroupGroupRemoved::class;
    }
}

class UserAssignedToRoleSubscriber implements DomainEventSubscriber
{
    private $test;

    public function __construct(RoleTest $test)
    {
        $this->test = $test;
    }

    public function handleEvent(DomainEvent $aDomainEvent)
    {
        $this->test->incrementRoleSomethingAssignedCount();
    }

    public function subscribedToEventType()
    {
        return UserAssignedToRole::class;
    }
}

class UserUnassignedFromRoleSubscriber implements DomainEventSubscriber
{
    private $test;

    public function __construct(RoleTest $test)
    {
        $this->test = $test;
    }

    public function handleEvent(DomainEvent $aDomainEvent)
    {
        $this->test->incrementRoleSomethingUnassignedCount();
    }

    public function subscribedToEventType()
    {
        return UserUnassignedFromRole::class;
    }
}

class GroupUserAddedSubscriber implements DomainEventSubscriber
{
    private $test;

    public function __construct(RoleTest $test)
    {
        $this->test = $test;
    }

    public function handleEvent(DomainEvent $aDomainEvent)
    {
        $this->test->incrementGroupSomethingAddedCount();
    }

    public function subscribedToEventType()
    {
        return GroupUserAdded::class;
    }
}

class GroupUserRemovedSubscriber implements DomainEventSubscriber
{
    private $test;

    public function __construct(RoleTest $test)
    {
        $this->test = $test;
    }

    public function handleEvent(DomainEvent $aDomainEvent)
    {
        $this->test->incrementGroupSomethingRemovedCount();
    }

    public function subscribedToEventType()
    {
        return GroupUserRemoved::class;
    }
}
