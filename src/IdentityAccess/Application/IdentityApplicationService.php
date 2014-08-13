<?php

namespace SaasOvation\IdentityAccess\Application;

use InvalidArgumentException;
use LogicException;
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
use SaasOvation\IdentityAccess\Application\Command\ProvisionGroupCommand;
use SaasOvation\IdentityAccess\Application\Command\ProvisionTenantCommand;
use SaasOvation\IdentityAccess\Application\Command\RegisterUserCommand;
use SaasOvation\IdentityAccess\Application\Command\RemoveGroupFromGroupCommand;
use SaasOvation\IdentityAccess\Application\Command\RemoveUserFromGroupCommand;
use SaasOvation\IdentityAccess\Domain\Model\Identity\AuthenticationService;
use SaasOvation\IdentityAccess\Domain\Model\Identity\ContactInformation;
use SaasOvation\IdentityAccess\Domain\Model\Identity\EmailAddress;
use SaasOvation\IdentityAccess\Domain\Model\Identity\Enablement;
use SaasOvation\IdentityAccess\Domain\Model\Identity\FullName;
use SaasOvation\IdentityAccess\Domain\Model\Identity\GroupMemberService;
use SaasOvation\IdentityAccess\Domain\Model\Identity\GroupRepository;
use SaasOvation\IdentityAccess\Domain\Model\Identity\Person;
use SaasOvation\IdentityAccess\Domain\Model\Identity\PostalAddress;
use SaasOvation\IdentityAccess\Domain\Model\Identity\Telephone;
use SaasOvation\IdentityAccess\Domain\Model\Identity\TenantId;
use SaasOvation\IdentityAccess\Domain\Model\Identity\TenantProvisioningService;
use SaasOvation\IdentityAccess\Domain\Model\Identity\TenantRepository;
use SaasOvation\IdentityAccess\Domain\Model\Identity\User;
use SaasOvation\IdentityAccess\Domain\Model\Identity\UserRepository;

class IdentityApplicationService
{
    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var GroupMemberService
     */
    private $groupMemberService;

    /**
     * @var GroupRepository
     */
    private $groupRepository;

    /**
     * @var TenantProvisioningService
     */
    private $tenantProvisioningService;

    /**
     * @var TenantRepository
     */
    private $tenantRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(
        AuthenticationService $authenticationService,
        GroupMemberService $groupMemberService,
        GroupRepository $groupRepository,
        TenantProvisioningService $tenantProvisioningService,
        TenantRepository $tenantRepository,
        UserRepository $userRepository
    ) {
        $this->authenticationService = $authenticationService;
        $this->groupMemberService = $groupMemberService;
        $this->groupRepository = $groupRepository;
        $this->tenantProvisioningService = $tenantProvisioningService;
        $this->tenantRepository = $tenantRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @Transactional
     */
    public function activateTenant(ActivateTenantCommand $aCommand)
    {
        $tenant = $this->existingTenant($aCommand->getTenantId());

        $tenant->activate();
    }

    /**
     * @Transactional
     */
    public function addGroupToGroup(AddGroupToGroupCommand $aCommand)
    {
        $parentGroup = $this->existingGroup(
            $aCommand->getTenantId(),
            $aCommand->getParentGroupName()
        );

        $childGroup = $this->existingGroup(
            $aCommand->getTenantId(),
            $aCommand->getChildGroupName()
        );

        $parentGroup->addGroup($childGroup, $this->groupMemberService());
    }


    public function addUserToGroup(AddUserToGroupCommand $aCommand)
    {
        $group = $this->existingGroup(
            $aCommand->getTenantId(),
            $aCommand->getGroupName()
        );

        $user = $this->existingUser(
            $aCommand->getTenantId(),
            $aCommand->getUsername()
        );

        $group->addUser($user);
    }

    /**
     * @Transactional(readOnly=true)
     */
    public function authenticateUser(AuthenticateUserCommand $aCommand)
    {
        $userDescriptor = $this->authenticationService()->authenticate(
            new TenantId($aCommand->getTenantId()),
            $aCommand->getUsername(),
            $aCommand->getPassword()
        );

        return $userDescriptor;
    }

    /**
     * @Transactional
     */
    public function deactivateTenant(DeactivateTenantCommand $aCommand)
    {
        $tenant = $this->existingTenant($aCommand->getTenantId());

        $tenant->deactivate();
    }

    /**
     * @Transactional
     */
    public function changeUserContactInformation(ChangeContactInfoCommand $aCommand)
    {
        $user = $this->existingUser($aCommand->getTenantId(), $aCommand->getUsername());

        $this->internalChangeUserContactInformation(
            $user,
            new ContactInformation(
                new EmailAddress($aCommand->getEmailAddress()),
                new PostalAddress(
                    $aCommand->getAddressStreetAddress(),
                    $aCommand->getAddressCity(),
                    $aCommand->getAddressStateProvince(),
                    $aCommand->getAddressPostalCode(),
                    $aCommand->getAddressCountryCode()
                ),
                new Telephone($aCommand->getPrimaryTelephone()),
                new Telephone($aCommand->getSecondaryTelephone())
            )
        );
    }

    /**
     * @Transactional
     */
    public function changeUserEmailAddress(ChangeEmailAddressCommand $aCommand)
    {
        $user = $this->existingUser($aCommand->getTenantId(), $aCommand->getUsername());

        $this->internalChangeUserContactInformation(
            $user,
            $user->person()->contactInformation()->changeEmailAddress(
                new EmailAddress($aCommand->getEmailAddress())
            )
        );
    }

    /**
     * @Transactional
     */
    public function changeUserPostalAddress(ChangePostalAddressCommand $aCommand)
    {
        $user = $this->existingUser($aCommand->getTenantId(), $aCommand->getUsername());

        $this->internalChangeUserContactInformation(
            $user,
            $user->person()->contactInformation()->changePostalAddress(
                new PostalAddress(
                    $aCommand->getAddressStreetAddress(),
                    $aCommand->getAddressCity(),
                    $aCommand->getAddressStateProvince(),
                    $aCommand->getAddressPostalCode(),
                    $aCommand->getAddressCountryCode()
                )
            )
        );
    }

    /**
     * @Transactional
     */
    public function changeUserPrimaryTelephone(ChangePrimaryTelephoneCommand $aCommand)
    {
        $user = $this->existingUser($aCommand->getTenantId(), $aCommand->getUsername());

        $this->internalChangeUserContactInformation(
            $user,
            $user->person()
            ->contactInformation()
            ->changePrimaryTelephone(new Telephone($aCommand->getTelephone())));
    }

    /**
     * @Transactional
     */
    public function changeUserSecondaryTelephone(ChangeSecondaryTelephoneCommand $aCommand)
    {
        $user = $this->existingUser($aCommand->getTenantId(), $aCommand->getUsername());

        $this->internalChangeUserContactInformation(
            $user,
            $user->person()
            ->contactInformation()
            ->changeSecondaryTelephone(new Telephone($aCommand->getTelephone())));
    }

    /**
     * @Transactional
     */
    public function changeUserPassword(ChangeUserPasswordCommand $aCommand)
    {
        $user = $this->existingUser($aCommand->getTenantId(), $aCommand->getUsername());

        $user->changePassword($aCommand->getCurrentPassword(), $aCommand->getChangedPassword());
    }

    /**
     * @Transactional
     */
    public function changeUserPersonalName(ChangeUserPersonalNameCommand $aCommand)
    {
        $user = $this->existingUser($aCommand->getTenantId(), $aCommand->getUsername());

        $user->person()->changeName(new FullName($aCommand->getFirstName(), $aCommand->getLastName()));
    }

    /**
     * @Transactional
     */
    public function defineUserEnablement(DefineUserEnablementCommand $aCommand)
    {
        $user = $this->existingUser($aCommand->getTenantId(), $aCommand->getUsername());

        $user->defineEnablement(
            new Enablement(
                $aCommand->isEnabled(),
                $aCommand->getStartDate(),
                $aCommand->getEndDate()
            )
        );
    }

    /**
     * @Transactional(readOnly=true)
     */
    public function group($aTenantId, $aGroupName)
    {
        $group = $this->groupRepository()->groupNamed(
            new TenantId($aTenantId),
            $aGroupName
        );

        return $group;
    }

    /**
     * @Transactional(readOnly=true)
     */
    public function isGroupMember($aTenantId, $aGroupName, $aUsername)
    {
        $group = $this->existingGroup(
            $aTenantId,
            $aGroupName
        );

        $user = $this->existingUser(
            $aTenantId,
            $aUsername
        );

        return $group->isMember($user, $this->groupMemberService());
    }

    /**
     * @Transactional
     */
    public function provisionGroup(ProvisionGroupCommand $aCommand)
    {
        $tenant = $this->existingTenant($aCommand->getTenantId());

        $group = $tenant->provisionGroup(
            $aCommand->getGroupName(),
            $aCommand->getDescription()
        );

        $this->groupRepository()->add($group);

        return $group;
    }

    /**
     * @Transactional
     */
    public function provisionTenant(ProvisionTenantCommand $aCommand)
    {
        return
            $this->tenantProvisioningService()->provisionTenant(
                $aCommand->getTenantName(),
                $aCommand->getTenantDescription(),
                new FullName(
                    $aCommand->getAdministorFirstName(),
                    $aCommand->getAdministorLastName()),
                new EmailAddress($aCommand->getEmailAddress()),
                new PostalAddress(
                    $aCommand->getAddressStateProvince(),
                    $aCommand->getAddressCity(),
                    $aCommand->getAddressStateProvince(),
                    $aCommand->getAddressPostalCode(),
                    $aCommand->getAddressCountryCode()),
                new Telephone($aCommand->getPrimaryTelephone()),
                new Telephone($aCommand->getSecondaryTelephone()));
    }

    /**
     * @Transactional
     */
    public function registerUser(RegisterUserCommand $aCommand)
    {
        $tenant = $this->existingTenant($aCommand->getTenantId());

        $user = $tenant->registerUser(
            $aCommand->getInvitationIdentifier(),
            $aCommand->getUsername(),
            $aCommand->getPassword(),
            new Enablement(
                $aCommand->isEnabled(),
                $aCommand->getStartDate(),
                $aCommand->getEndDate()
            ),
            new Person(
                new TenantId($aCommand->getTenantId()),
                new FullName($aCommand->getFirstName(), $aCommand->getLastName()),
                new ContactInformation(
                    new EmailAddress($aCommand->getEmailAddress()),
                    new PostalAddress(
                        $aCommand->getAddressStateProvince(),
                        $aCommand->getAddressCity(),
                        $aCommand->getAddressStateProvince(),
                        $aCommand->getAddressPostalCode(),
                        $aCommand->getAddressCountryCode()
                    ),
                    new Telephone($aCommand->getPrimaryTelephone()),
                    new Telephone($aCommand->getSecondaryTelephone())
                )
            )
        );

        if ($user === null) {
            throw new LogicException('User not registered.');
        }

        $this->userRepository()->add($user);

        return $user;
    }

    /**
     * @Transactional
     */
    public function removeGroupFromGroup(RemoveGroupFromGroupCommand $aCommand)
    {
        $parentGroup = $this->existingGroup(
            $aCommand->getTenantId(),
            $aCommand->getParentGroupName()
        );

        $childGroup = $this->existingGroup(
            $aCommand->getTenantId(),
            $aCommand->getChildGroupName()
        );

        $parentGroup->removeGroup($childGroup);
    }

    /**
     * @Transactional
     */
    public function removeUserFromGroup(RemoveUserFromGroupCommand $aCommand)
    {
        $group = $this->existingGroup(
            $aCommand->getTenantId(),
            $aCommand->getGroupName()
        );

        $user = $this->existingUser(
            $aCommand->getTenantId(),
            $aCommand->getUsername()
        );

        $group->removeUser($user);
    }

    /**
     * @Transactional(readOnly=true)
     */
    public function tenant($aTenantId)
    {
        $tenant = $this->tenantRepository()->tenantOfId(new TenantId($aTenantId));

        return $tenant;
    }

    /**
     * @Transactional(readOnly=true)
     */
    public function user($aTenantId, $aUsername)
    {
        return $this->userRepository()->userWithUsername(
            new TenantId($aTenantId),
            $aUsername
        );
    }

    /**
     * @Transactional(readOnly=true)
     */
    public function userDescriptor($aTenantId, $aUsername)
    {
        $userDescriptor = null;

        $user = $this->user($aTenantId, $aUsername);

        if (null !== $user) {
            $userDescriptor = $user->userDescriptor();
        }

        return $userDescriptor;
    }

    private function authenticationService()
    {
        return $this->authenticationService;
    }

    private function existingGroup($aTenantId, $aGroupName)
    {
        $group = $this->group($aTenantId, $aGroupName);

        if (null === $group) {
            throw new InvalidArgumentException('Group does not exist for: ' . $aTenantId . ' and: ' . $aGroupName);
        }

        return $group;
    }

    private function existingTenant($aTenantId)
    {
        $tenant = $this->tenant($aTenantId);

        if (null === $tenant) {
            throw new InvalidArgumentException('Tenant does not exist for: ' . $aTenantId);
        }

        return $tenant;
    }

    private function existingUser($aTenantId, $aUsername)
    {
        $user = $this->user($aTenantId, $aUsername);

        if (null === $user) {
            throw new InvalidArgumentException('User does not exist for: ' . $aTenantId . ' and ' . $aUsername);
        }

        return $user;
    }

    private function groupMemberService()
    {
        return $this->groupMemberService;
    }

    private function groupRepository()
    {
        return $this->groupRepository;
    }

    private function internalChangeUserContactInformation(User $aUser, ContactInformation $aContactInformation)
    {
        $aUser->person()->changeContactInformation($aContactInformation);
    }

    private function tenantProvisioningService()
    {
        return $this->tenantProvisioningService;
    }

    private function tenantRepository()
    {
        return $this->tenantRepository;
    }

    private function userRepository()
    {
        return $this->userRepository;
    }
}
