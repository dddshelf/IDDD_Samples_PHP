<?php

namespace SaasOvation\IdentityAccess\Test\Application;

use DateTime;
use SaasOvation\IdentityAccess\Application\ApplicationServiceRegistry;
use SaasOvation\IdentityAccess\Application\Command\ActivateTenantCommand;
use SaasOvation\IdentityAccess\Application\Command\AddGroupToGroupCommand;
use SaasOvation\IdentityAccess\Application\Command\AddUserToGroupCommand;
use SaasOvation\IdentityAccess\Application\Command\AuthenticateUserCommand;
use SaasOvation\IdentityAccess\Application\Command\ChangeContactInfoCommand;
use SaasOvation\IdentityAccess\Application\Command\ChangeEmailAddressCommand;
use SaasOvation\IdentityAccess\Application\Command\ChangePostalAddressCommand;
use SaasOvation\IdentityAccess\Application\Command\ChangePrimaryTelephoneCommand;
use SaasOvation\IdentityAccess\Application\Command\ChangeSecondaryTelephoneCommand;
use SaasOvation\IdentityAccess\Application\Command\ChangeUserPasswordCommand;
use SaasOvation\IdentityAccess\Application\Command\ChangeUserPersonalNameCommand;
use SaasOvation\IdentityAccess\Application\Command\DeactivateTenantCommand;
use SaasOvation\IdentityAccess\Application\Command\DefineUserEnablementCommand;
use SaasOvation\IdentityAccess\Application\Command\RemoveGroupFromGroupCommand;
use SaasOvation\IdentityAccess\Application\Command\RemoveUserFromGroupCommand;
use SaasOvation\IdentityAccess\Domain\Model\DomainRegistry;

class IdentityApplicationServiceTest extends ApplicationServiceTest
{
    public function testActivateTenant()
    {
        $tenant = $this->tenantAggregate();
        $tenant->deactivate();
        $this->assertFalse($tenant->isActive());

        ApplicationServiceRegistry::identityApplicationService()->activateTenant(
            new ActivateTenantCommand($tenant->tenantId()->id())
        );

        $changedTenant = DomainRegistry::tenantRepository()->tenantOfId($tenant->tenantId());

        $this->assertNotNull($changedTenant);
        $this->assertEquals($tenant->name(), $changedTenant->name());
        $this->assertTrue($changedTenant->isActive());
    }

    public function testAddGroupToGroup()
    {
        $parentGroup = $this->group1Aggregate();
        DomainRegistry::groupRepository()->add($parentGroup);

        $childGroup = $this->group2Aggregate();
        DomainRegistry::groupRepository()->add($childGroup);

        $this->assertEquals(0, $parentGroup->groupMembers()->count());

        ApplicationServiceRegistry::identityApplicationService()->addGroupToGroup(
            new AddGroupToGroupCommand(
                $parentGroup->tenantId()->id(),
                $parentGroup->name(),
                $childGroup->name()
            )
        );

        $this->assertEquals(1, $parentGroup->groupMembers()->count());
    }

    public function testAddUserToGroup()
    {
        $parentGroup = $this->group1Aggregate();
        DomainRegistry::groupRepository()->add($parentGroup);

        $childGroup = $this->group2Aggregate();
        DomainRegistry::groupRepository()->add($childGroup);

        $user = $this->userAggregate();
        DomainRegistry::userRepository()->add($user);

        $this->assertEquals(0, $parentGroup->groupMembers()->count());
        $this->assertEquals(0, $childGroup->groupMembers()->count());

        $parentGroup->addGroup($childGroup, DomainRegistry::groupMemberService());

        ApplicationServiceRegistry::identityApplicationService()->addUserToGroup(
            new AddUserToGroupCommand(
                $childGroup->tenantId()->id(),
                $childGroup->name(),
                $user->username()
            )
        );

        $this->assertEquals(1, $parentGroup->groupMembers()->count());
        $this->assertEquals(1, $childGroup->groupMembers()->count());
        $this->assertTrue($parentGroup->isMember($user, DomainRegistry::groupMemberService()));
        $this->assertTrue($childGroup->isMember($user, DomainRegistry::groupMemberService()));
    }

    public function testAuthenticateUser()
    {
        $user = $this->userAggregate();
        DomainRegistry::userRepository()->add($user);

        $userDescriptor = ApplicationServiceRegistry::identityApplicationService()->authenticateUser(
            new AuthenticateUserCommand(
                $user->tenantId()->id(),
                $user->username(),
                self::$FIXTURE_PASSWORD
            )
        );

        $this->assertNotNull($userDescriptor);
        $this->assertEquals($user->username(), $userDescriptor->username());
    }

    public function testDeactivateTenant()
    {
        $tenant = $this->tenantAggregate();
        $this->assertTrue($tenant->isActive());

        ApplicationServiceRegistry::identityApplicationService()->deactivateTenant(
            new DeactivateTenantCommand($tenant->tenantId()->id())
        );

        $changedTenant = DomainRegistry::tenantRepository()->tenantOfId($tenant->tenantId());

        $this->assertNotNull($changedTenant);
        $this->assertEquals($tenant->name(), $changedTenant->name());
        $this->assertFalse($changedTenant->isActive());
    }

    public function testChangeUserContactInformation()
    {
        $user = $this->userAggregate();
        DomainRegistry::userRepository()->add($user);

        ApplicationServiceRegistry::identityApplicationService()->changeUserContactInformation(
            new ChangeContactInfoCommand(
                $user->tenantId()->id(),
                $user->username(),
                'mynewemailaddress@saasovation.com',
                '777-555-1211',
                '777-555-1212',
                '123 Pine Street',
                'Loveland',
                'CO',
                '80771',
                'US'
            )
        );

        $changedUser = DomainRegistry::userRepository()->userWithUsername(
            $user->tenantId(),
            $user->username()
        );

        $this->assertNotNull($changedUser);
        $this->assertEquals('mynewemailaddress@saasovation.com', $changedUser->person()->emailAddress()->address());
        $this->assertEquals('777-555-1211', $changedUser->person()->contactInformation()->primaryTelephone()->number());
        $this->assertEquals('777-555-1212', $changedUser->person()->contactInformation()->secondaryTelephone()->number());
        $this->assertEquals('123 Pine Street', $changedUser->person()->contactInformation()->postalAddress()->streetAddress());
        $this->assertEquals('Loveland', $changedUser->person()->contactInformation()->postalAddress()->city());
    }

    public function testChangeUserEmailAddress()
    {
        $user = $this->userAggregate();
        DomainRegistry::userRepository()->add($user);

        ApplicationServiceRegistry::identityApplicationService()->changeUserEmailAddress(
            new ChangeEmailAddressCommand(
                $user->tenantId()->id(),
                $user->username(),
                'mynewemailaddress@saasovation.com'
            )
        );

        $changedUser = DomainRegistry::userRepository()->userWithUsername(
            $user->tenantId(),
            $user->username()
        );

        $this->assertNotNull($changedUser);
        $this->assertEquals('mynewemailaddress@saasovation.com', $changedUser->person()->emailAddress()->address());
    }

    public function testChangeUserPostalAddress()
    {
        $user = $this->userAggregate();
        DomainRegistry::userRepository()->add($user);

        ApplicationServiceRegistry::identityApplicationService()->changeUserPostalAddress(
            new ChangePostalAddressCommand(
                $user->tenantId()->id(),
                $user->username(),
                '123 Pine Street',
                'Loveland',
                'CO',
                '80771',
                'US'
            )
        );

        $changedUser = DomainRegistry::userRepository()->userWithUsername(
            $user->tenantId(),
            $user->username()
        );

        $this->assertNotNull($changedUser);
        $this->assertEquals('123 Pine Street', $changedUser->person()->contactInformation()->postalAddress()->streetAddress());
        $this->assertEquals('Loveland', $changedUser->person()->contactInformation()->postalAddress()->city());
    }

    public function testChangeUserPrimaryTelephone()
    {
        $user = $this->userAggregate();
        DomainRegistry::userRepository()->add($user);

        ApplicationServiceRegistry::identityApplicationService()->changeUserPrimaryTelephone(
            new ChangePrimaryTelephoneCommand(
                $user->tenantId()->id(),
                $user->username(),
                '777-555-1211'
            )
        );

        $changedUser = DomainRegistry::userRepository()->userWithUsername(
            $user->tenantId(),
            $user->username()
        );

        $this->assertNotNull($changedUser);
        $this->assertEquals('777-555-1211', $changedUser->person()->contactInformation()->primaryTelephone()->number());
    }

    public function testChangeUserSecondaryTelephone()
    {
        $user = $this->userAggregate();
        DomainRegistry::userRepository()->add($user);

        ApplicationServiceRegistry::identityApplicationService()->changeUserSecondaryTelephone(
            new ChangeSecondaryTelephoneCommand(
                $user->tenantId()->id(),
                $user->username(),
                '777-555-1212'
            )
        );

        $changedUser = DomainRegistry::userRepository()->userWithUsername(
            $user->tenantId(),
            $user->username()
        );

        $this->assertNotNull($changedUser);
        $this->assertEquals('777-555-1212', $changedUser->person()->contactInformation()->secondaryTelephone()->number());
    }

    public function testChangeUserPassword()
    {
        $user = $this->userAggregate();
        DomainRegistry::userRepository()->add($user);

        ApplicationServiceRegistry::identityApplicationService()->changeUserPassword(
            new ChangeUserPasswordCommand(
                $user->tenantId()->id(),
                $user->username(),
                self::$FIXTURE_PASSWORD,
                'THIS.IS.JOE\'S.NEW.PASSWORD'
            )
        );

        $userDescriptor = ApplicationServiceRegistry::identityApplicationService()->authenticateUser(
            new AuthenticateUserCommand(
                $user->tenantId()->id(),
                $user->username(),
                'THIS.IS.JOE\'S.NEW.PASSWORD'
            )
        );

        $this->assertNotNull($userDescriptor);
        $this->assertEquals($user->username(), $userDescriptor->username());
    }

    public function testChangeUserPersonalName()
    {
        $user = $this->userAggregate();
        DomainRegistry::userRepository()->add($user);

        ApplicationServiceRegistry::identityApplicationService()->changeUserPersonalName(
            new ChangeUserPersonalNameCommand(
                $user->tenantId()->id(),
                $user->username(),
                'World',
                'Peace'
            )
        );

        $changedUser = DomainRegistry::userRepository()->userWithUsername(
            $user->tenantId(),
            $user->username()
        );

        $this->assertNotNull($changedUser);
        $this->assertEquals('World Peace', $changedUser->person()->name()->asFormattedName());
    }

    public function testDefineUserEnablement()
    {
        $user = $this->userAggregate();
        DomainRegistry::userRepository()->add($user);

        $now = new DateTime();
        $then = (new DateTime())->setTimestamp($now->getTimestamp() + (60 * 60 * 24 * 365 * 1000));

        ApplicationServiceRegistry::identityApplicationService()->defineUserEnablement(
            new DefineUserEnablementCommand(
                $user->tenantId()->id(),
                $user->username(),
                true,
                $now,
                $then
            )
        );

        $changedUser = DomainRegistry::userRepository()->userWithUsername(
            $user->tenantId(),
            $user->username()
        );

        $this->assertNotNull($changedUser);
        $this->assertTrue($changedUser->isEnabled());
    }

    public function testIsGroupMember()
    {
        $parentGroup = $this->group1Aggregate();
        DomainRegistry::groupRepository()->add($parentGroup);

        $childGroup = $this->group2Aggregate();
        DomainRegistry::groupRepository()->add($childGroup);

        $user = $this->userAggregate();
        DomainRegistry::userRepository()->add($user);

        $this->assertEquals(0, $parentGroup->groupMembers()->count());
        $this->assertEquals(0, $childGroup->groupMembers()->count());

        $parentGroup->addGroup($childGroup, DomainRegistry::groupMemberService());
        $childGroup->addUser($user);

        $this->assertTrue(
            ApplicationServiceRegistry::identityApplicationService()->isGroupMember(
                $parentGroup->tenantId()->id(),
                $parentGroup->name(),
                $user->username()
            )
        );

        $this->assertTrue(
            ApplicationServiceRegistry::identityApplicationService()->isGroupMember(
                $childGroup->tenantId()->id(),
                $childGroup->name(),
                $user->username()
            )
        );
    }

    public function testRemoveGroupFromGroup()
    {
        $parentGroup = $this->group1Aggregate();
        DomainRegistry::groupRepository()->add($parentGroup);

        $childGroup = $this->group2Aggregate();
        DomainRegistry::groupRepository()->add($childGroup);

        $parentGroup->addGroup($childGroup, DomainRegistry::groupMemberService());

        $this->assertEquals(1, $parentGroup->groupMembers()->count());

        ApplicationServiceRegistry::identityApplicationService()->removeGroupFromGroup(
            new RemoveGroupFromGroupCommand(
                $parentGroup->tenantId()->id(),
                $parentGroup->name(),
                $childGroup->name()
            )
        );

        $this->assertEquals(0, $parentGroup->groupMembers()->count());
    }

    public function testRemoveUserFromGroup()
    {
        $parentGroup = $this->group1Aggregate();
        DomainRegistry::groupRepository()->add($parentGroup);

        $childGroup = $this->group2Aggregate();
        DomainRegistry::groupRepository()->add($childGroup);

        $user = $this->userAggregate();
        DomainRegistry::userRepository()->add($user);

        $parentGroup->addGroup($childGroup, DomainRegistry::groupMemberService());
        $childGroup->addUser($user);

        $this->assertEquals(1, $parentGroup->groupMembers()->count());
        $this->assertEquals(1, $childGroup->groupMembers()->count());
        $this->assertTrue($parentGroup->isMember($user, DomainRegistry::groupMemberService()));
        $this->assertTrue($childGroup->isMember($user, DomainRegistry::groupMemberService()));

        ApplicationServiceRegistry::identityApplicationService()->removeUserFromGroup(
            new RemoveUserFromGroupCommand(
                $childGroup->tenantId()->id(),
                $childGroup->name(),
                $user->username()
            )
        );

        $this->assertEquals(1, $parentGroup->groupMembers()->count());
        $this->assertEquals(0, $childGroup->groupMembers()->count());
        $this->assertFalse($parentGroup->isMember($user, DomainRegistry::groupMemberService()));
        $this->assertFalse($childGroup->isMember($user, DomainRegistry::groupMemberService()));
    }

    public function testQueryTenant()
    {
        $tenant = $this->tenantAggregate();

        $queriedTenant = ApplicationServiceRegistry::identityApplicationService()->tenant($tenant->tenantId()->id());

        $this->assertNotNull($queriedTenant);
        $this->assertEquals($tenant, $queriedTenant);
    }

    public function testQueryUser()
    {
        $user = $this->userAggregate();
        DomainRegistry::userRepository()->add($user);

        $queriedUser = ApplicationServiceRegistry::identityApplicationService()->user(
            $user->tenantId()->id(),
            $user->username()
        );

        $this->assertNotNull($user);
        $this->assertEquals($user, $queriedUser);
    }

    public function testQueryUserDescriptor()
    {
        $user = $this->userAggregate();
        DomainRegistry::userRepository()->add($user);

        $queriedUserDescriptor = ApplicationServiceRegistry::identityApplicationService()->userDescriptor(
            $user->tenantId()->id(),
            $user->username()
        );

        $this->assertNotNull($user);
        $this->assertEquals($user->userDescriptor(), $queriedUserDescriptor);
    }
}
