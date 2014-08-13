<?php

namespace SaasOvation\IdentityAccess\Domain\Model\Identity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Exception;
use InvalidArgumentException;
use Rhumsaa\Uuid\Uuid;
use SaasOvation\Common\Domain\Model\ConcurrencySafeEntity;
use SaasOvation\Common\Domain\Model\DomainEventPublisher;

class Group extends ConcurrencySafeEntity
{
    public static $ROLE_GROUP_PREFIX = 'ROLE-INTERNAL-GROUP: ';

    /**
     * @var string
     */
    private $description;

    /**
     * @var Collection
     */
    private $groupMembers;

    /**
     * @var string
     */
    private $name;

    /**
     * @var TenantId
     */
    private $tenantId;

    public function __construct(TenantId $aTenantId, $aName, $aDescription)
    {
        $this->setGroupMembers(new ArrayCollection());
        $this->setDescription($aDescription);
        $this->setName($aName);
        $this->setTenantId($aTenantId);
    }

    public function addGroup(Group $aGroup, GroupMemberService $aGroupMemberService)
    {
        $this->assertArgumentNotNull($aGroup, 'Group must not be null.');
        $this->assertArgumentEquals($this->tenantId(), $aGroup->tenantId(), 'Wrong tenant for this group.');
        $this->assertArgumentFalse($aGroupMemberService->isMemberGroup($aGroup, $this->toGroupMember()), 'Group recurrsion.');

        if ($this->groupMembers()->add($aGroup->toGroupMember()) && !$this->isInternalGroup($this->name())) {
            DomainEventPublisher::instance()->publish(
                new GroupGroupAdded(
                    $this->tenantId(),
                    $this->name(),
                    $aGroup->name()
                )
            );
        }
    }

    public function addUser(User $aUser)
    {
        $this->assertArgumentNotNull($aUser, 'User must not be null.');
        $this->assertArgumentEquals($this->tenantId(), $aUser->tenantId(), 'Wrong tenant for this group.');
        $this->assertArgumentTrue($aUser->isEnabled(), 'User is not enabled.');

        if ($this->groupMembers()->add($aUser->toGroupMember()) && !$this->isInternalGroup($this->name())) {
            DomainEventPublisher::instance()->publish(
                new GroupUserAdded(
                    $this->tenantId(),
                    $this->name(),
                    $aUser->username()
                )
            );
        }
    }

    public function description()
    {
        return $this->description;
    }

    public function groupMembers()
    {
        return $this->groupMembers;
    }

    public function isMember(User $aUser, GroupMemberService $aGroupMemberService)
    {
        $this->assertArgumentNotNull($aUser, 'User must not be null.');
        $this->assertArgumentEquals($this->tenantId(), $aUser->tenantId(), 'Wrong tenant for this group.');
        $this->assertArgumentTrue($aUser->isEnabled(), 'User is not enabled.');

        $isMember = $this->groupMembers()->filter(function(GroupMember $aGroupMember) use ($aUser) {
            return $aGroupMember == $aUser->toGroupMember();
        })->count() > 0;

        if ($isMember) {
            $isMember = $aGroupMemberService->confirmUser($this, $aUser);
        } else {
            $isMember = $aGroupMemberService->isUserInNestedGroup($this, $aUser);
        }

        return $isMember;
    }

    public function name()
    {
        return $this->name;
    }

    public function removeGroup(Group $aGroup)
    {
        $this->assertArgumentNotNull($aGroup, 'Group must not be null.');
        $this->assertArgumentEquals($this->tenantId(), $aGroup->tenantId(), 'Wrong tenant for this group.');

        $groupMemberRemoved = false;

        $this->setGroupMembers(
            new ArrayCollection(
                array_filter(
                    $this->groupMembers()->toArray(),
                    function (GroupMember $aGroupMember) use (&$groupMemberRemoved, $aGroup) {
                        if ($aGroupMember->equals($aGroup->toGroupMember())) {
                            $groupMemberRemoved = true;
                            return false;
                        }

                        return true;
                    }
                )
            )
        );

        // not a nested remove, only direct member
        if ($groupMemberRemoved && !$this->isInternalGroup($this->name())) {
            DomainEventPublisher::instance()->publish(
                new GroupGroupRemoved(
                    $this->tenantId(),
                    $this->name(),
                    $aGroup->name()
                )
            );
        }
    }

    public function removeUser(User $aUser)
    {
        $this->assertArgumentNotNull($aUser, 'User must not be null.');
        $this->assertArgumentEquals($this->tenantId(), $aUser->tenantId(), 'Wrong tenant for this group.');

        $groupMemberRemoved = false;

        $this->setGroupMembers(
            new ArrayCollection(
                array_filter(
                    $this->groupMembers()->toArray(),
                    function (GroupMember $aGroupMember) use (&$groupMemberRemoved, $aUser) {
                        if ($aGroupMember->equals($aUser->toGroupMember())) {
                            $groupMemberRemoved = true;

                            return false;
                        }

                        return true;
                    }
                )
            )
        );

        // not a nested remove, only direct member
        if ($groupMemberRemoved && !$this->isInternalGroup($this->name())) {
            DomainEventPublisher::instance()->publish(
                new GroupUserRemoved(
                    $this->tenantId(),
                    $this->name(),
                    $aUser->username()
                )
            );
        }
    }

    public function tenantId()
    {
        return $this->tenantId;
    }

    public function equals($anObject)
    {
        $equalObjects = false;

        if (null !== $anObject && get_class($this) === get_class($anObject)) {
            $equalObjects =
                   $this->tenantId()->equals($anObject->tenantId())
                && $this->name()->equals($anObject->name())
            ;
        }

        return $equalObjects;
    }

    public function __toString()
    {
        return 'Group [description=' . $this->description . ', name=' . $this->name . ', tenantId=' . $this->tenantId . ']';
    }

    protected function setDescription($aDescription)
    {
        $this->assertArgumentNotEmpty($aDescription, 'Group description is required.');
        $this->assertArgumentLength($aDescription, 1, 250, 'Group description must be 250 characters or less.');

        $this->description = $aDescription;
    }

    protected function setGroupMembers(Collection $aGroupMembers)
    {
        $this->groupMembers = $aGroupMembers;
    }

    protected function isInternalGroup($aName)
    {
        return 0 === strpos($aName, self::$ROLE_GROUP_PREFIX);
    }

    protected function setName($aName)
    {
        $this->assertArgumentNotEmpty($aName, 'Group name is required.');
        $this->assertArgumentLength($aName, 1, 100, 'Group name must be 100 characters or less.');

        if ($this->isInternalGroup($aName)) {
            $uuid = substr($aName, strlen(self::$ROLE_GROUP_PREFIX));

                try {
                    Uuid::fromString($uuid);
                } catch (Exception $e) {
                    throw new InvalidArgumentException('The group name has an invalid format.');
                }
            }

        $this->name = $aName;
    }

    protected function setTenantId(TenantId $aTenantId)
    {
        $this->assertArgumentNotNull($aTenantId, 'The tenantId must be provided.');

        $this->tenantId = $aTenantId;
    }

    protected function toGroupMember()
    {
        $groupMember = new GroupMember(
            $this->tenantId(),
            $this->name(),
            new GroupMemberType\Group()
        );

        return $groupMember;
    }
}
