<?php

namespace SaasOvation\IdentityAccess\Domain\Model\Identity;

use SaasOvation\Common\AssertionConcern;

class AuthenticationService extends AssertionConcern
{
    /**
     * @var EncryptionService
     */
    private $encryptionService;

    /**
     * @var TenantRepository
     */
    private $tenantRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(
        TenantRepository $aTenantRepository,
        UserRepository $aUserRepository,
        EncryptionService $anEncryptionService
    ) {
        $this->encryptionService = $anEncryptionService;
        $this->tenantRepository = $aTenantRepository;
        $this->userRepository = $aUserRepository;
    }

    public function authenticate(TenantId $aTenantId, $aUsername, $aPassword)
    {
        $this->assertArgumentNotNull($aTenantId, 'TenantId must not be null.');
        $this->assertArgumentNotEmpty($aUsername, 'Username must be provided.');
        $this->assertArgumentNotEmpty($aPassword, 'Password must be provided.');

        $userDescriptor = UserDescriptor::nullDescriptorInstance();

        $tenant = $this->tenantRepository()->tenantOfId($aTenantId);

        if (null !== $tenant && $tenant->isActive()) {
            $encryptedPassword = $this->encryptionService()->encryptedValue($aPassword);

            $user = $this->userRepository()->userFromAuthenticCredentials(
                $aTenantId,
                $aUsername,
                $encryptedPassword
            );

            if (null !== $user && $user->isEnabled()) {
                $userDescriptor = $user->userDescriptor();
            }
        }

        return $userDescriptor;
    }

    private function encryptionService()
    {
        return $this->encryptionService;
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
