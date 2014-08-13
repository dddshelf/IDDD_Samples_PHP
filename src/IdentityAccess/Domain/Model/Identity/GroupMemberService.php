<?php

namespace SaasOvation\IdentityAccess\Domain\Model\Identity;

class GroupMemberService
{
    /**
     * @var GroupRepository
     */
    private $groupRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(
        UserRepository $aUserRepository,
        GroupRepository $aGroupRepository
    ) {
        $this->groupRepository = $aGroupRepository;
        $this->userRepository  = $aUserRepository;
    }

    public function confirmUser(Group $aGroup, User $aUser)
    {
        $userConfirmed = true;

        $confirmedUser = $this->userRepository()->userWithUsername($aGroup->tenantId(), $aUser->username());

        if (null == $confirmedUser || !$confirmedUser->isEnabled()) {
            $userConfirmed = false;
        }

        return $userConfirmed;
    }

    public function isMemberGroup(Group $aGroup, GroupMember $aMemberGroup)
    {
        $isMember = false;

        $iter = $aGroup->groupMembers()->getIterator();

        while (!$isMember && $iter->valid()) {
            $member = $iter->current();
            if ($member->isGroup()) {
                if ($aMemberGroup->equals($member)) {
                    $isMember = true;
                } else {
                    $group = $this->groupRepository()->groupNamed(
                        $member->tenantId(),
                        $member->name()
                    );

                    if (null !== $group) {
                        $isMember = $this->isMemberGroup($group, $aMemberGroup);
                    }
                }
            }

            $iter->next();
        }

        return $isMember;
    }

    public function isUserInNestedGroup(Group $aGroup, User $aUser)
    {
        $isInNestedGroup = false;

        $iter = $aGroup->groupMembers()->getIterator();

        while (!$isInNestedGroup && $iter->valid()) {
            $member = $iter->current();
            if ($member->isGroup()) {
                $group = $this->groupRepository()->groupNamed(
                    $member->tenantId(),
                    $member->name()
                );

                if (null !== $group) {
                    $isInNestedGroup = $group->isMember($aUser, $this);
                }
            }

            $iter->next();
        }

        return $isInNestedGroup;
    }

    private function groupRepository()
    {
        return $this->groupRepository;
    }

    private function userRepository()
    {
        return $this->userRepository;
    }
}
