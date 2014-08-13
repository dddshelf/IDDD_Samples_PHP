<?php

namespace SaasOvation\IdentityAccess\Domain\Model\Access;

use Rhumsaa\Uuid\Uuid;
use SaasOvation\Common\Domain\Model\ConcurrencySafeEntity;
use SaasOvation\Common\Domain\Model\DomainEventPublisher;
use SaasOvation\IdentityAccess\Domain\Model\Identity\Group;
use SaasOvation\IdentityAccess\Domain\Model\Identity\GroupMemberService;
use SaasOvation\IdentityAccess\Domain\Model\Identity\TenantId;
use SaasOvation\IdentityAccess\Domain\Model\Identity\User;

class Role extends ConcurrencySafeEntity
{
    /**
     * @var string
     */
    private $description;

    /**
     * @var Group
     */
    private $group;

    /**
     * @var string
     */
    private $name;

    /**
     * @var bool
     */
    private $supportsNesting = true;

    /**
     * @var TenantId
     */
    private $tenantId;

    public function __construct(
        TenantId $aTenantId,
        $aName,
        $aDescription,
        $aSupportsNesting = false
    ) {
        $this->setDescription($aDescription);
        $this->setName($aName);
        $this->setSupportsNesting($aSupportsNesting);
        $this->setTenantId($aTenantId);
    
        $this->createInternalGroup();
    }

    public function assignGroup(Group $aGroup, GroupMemberService $aGroupMemberService)
    {
        $this->assertStateTrue($this->supportsNesting(), 'this role does not support group nesting.');
        $this->assertArgumentNotNull($aGroup, 'Group must not be null.');
        $this->assertArgumentEquals($this->tenantId(), $aGroup->tenantId(), 'Wrong tenant for this group.');

        $this->group()->addGroup($aGroup, $aGroupMemberService);

        DomainEventPublisher::instance()->publish(
            new GroupAssignedToRole(
                $this->tenantId(),
                $this->name(),
                $aGroup->name()
            )
        );
    }

    public function assignUser(User $aUser)
    {
        $this->assertArgumentNotNull($aUser, 'User must not be null.');
        $this->assertArgumentEquals($this->tenantId(), $aUser->tenantId(), 'Wrong tenant for this user.');

        $this->group()->addUser($aUser);

        // NOTE: Consider what a consuming Bounded Context would
        // need to do if $this event was not enriched with the
        // last three user person properties. (Hint: A lot.)

        DomainEventPublisher::instance()->publish(
            new UserAssignedToRole(
                $this->tenantId(),
                $this->name(),
                $aUser->username(),
                $aUser->person()->name()->firstName(),
                $aUser->person()->name()->lastName(),
                $aUser->person()->emailAddress()->address()
            )
        );
    }

    public function description()
    {
        return $this->description;
    }

    public function isInRole(User $aUser, GroupMemberService $aGroupMemberService)
    {
        return $this->group()->isMember($aUser, $aGroupMemberService);
    }

    public function name()
    {
        return $this->name;
    }

    public function supportsNesting()
    {
        return $this->supportsNesting;
    }

    public function tenantId()
    {
        return $this->tenantId;
    }

    public function unassignGroup(Group $aGroup)
    {
        $this->assertStateTrue($this->supportsNesting(), 'This role does not support group nesting.');
        $this->assertArgumentNotNull($aGroup, 'Group must not be null.');
        $this->assertArgumentEquals($this->tenantId(), $aGroup->tenantId(), 'Wrong tenant for this group.');

        $this->group()->removeGroup($aGroup);

        DomainEventPublisher::instance()->publish(
            new GroupUnassignedFromRole(
                $this->tenantId(),
                $this->name(),
                $aGroup->name()
            )
        );
    }

    public function unassignUser(User $aUser)
    {
        $this->assertArgumentNotNull($aUser, 'User must not be null.');
        $this->assertArgumentEquals($this->tenantId(), $aUser->tenantId(), 'Wrong tenant for this user.');
    
        $this->group()->removeUser($aUser);
    
        DomainEventPublisher::instance()->publish(
            new UserUnassignedFromRole(
                $this->tenantId(),
                $this->name(),
                $aUser->username()
            )
        );
    }

    public function equals($anObject)
    {
        $equalObjects = false;

        if (null !== $anObject && get_class($this) == get_class($anObject)) {
            $equalObjects =
                $this->tenantId()->equals($anObject->tenantId()) &&
                $this->name()->equals($anObject->name());
        }

        return $equalObjects;
    }

    public function __toString()
    {
        return 'Role [tenantId=' . $this->tenantId . ', name=' . $this->name . ', description=' . $this->description . ', supportsNesting=' . $this->supportsNesting . ', group=' . $this->group . ']';
    }

    protected function createInternalGroup()
    {
        $groupName = Group::$ROLE_GROUP_PREFIX . strtoupper(Uuid::uuid4());

        $this->setGroup(
            new Group(
                $this->tenantId(),
                $groupName,
                'Role backing group for: ' . $this->name()
            )
        );
    }

    protected function setDescription($aDescription)
    {
        $this->assertArgumentNotEmpty($aDescription, 'Role description is required.');
        $this->assertArgumentLength($aDescription, 1, 250, 'Role description must be 250 characters or less.');

        $this->description = $aDescription;
    }

    public function group()
    {
        return $this->group;
    }

    protected function setGroup(Group $aGroup)
    {
        $this->group = $aGroup;
    }

    protected function setName($aName)
    {
        $this->assertArgumentNotEmpty($aName, 'Role name must be provided.');
        $this->assertArgumentLength($aName, 1, 250, 'Role name must be 100 characters or less.');

        $this->name = $aName;
    }

    protected function setSupportsNesting($aSupportsNesting)
    {
        $this->supportsNesting = $aSupportsNesting;
    }

    protected function setTenantId(TenantId $aTenantId)
    {
        $this->assertArgumentNotNull($aTenantId, 'The tenantId is required.');

        $this->tenantId = $aTenantId;
    }
}
