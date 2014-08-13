<?php

namespace SaasOvation\IdentityAccess\Domain\Model\Identity;

use Doctrine\Common\Collections\Collection;

interface UserRepository
{
    /**
     * @param User $aUser
     *
     * @return void
     */
    public function add(User $aUser);

    /**
     * @param TenantId $aTenantId
     * @param string $aFirstNamePrefix
     * @param string $aLastNamePrefix
     *
     * @return Collection
     */
    public function allSimilarlyNamedUsers(TenantId $aTenantId, $aFirstNamePrefix, $aLastNamePrefix);

    /**
     * @param User $aUser
     *
     * @return void
     */
    public function remove(User $aUser);

    /**
     * @param TenantId $aTenantId
     * @param string $aUsername
     * @param string $anEncryptedPassword
     *
     * @return User
     */
    public function userFromAuthenticCredentials(TenantId $aTenantId, $aUsername, $anEncryptedPassword);

    /**
     * @param TenantId $aTenantId
     * @param string $aUsername
     *
     * @return User
     */
    public function userWithUsername(TenantId $aTenantId, $aUsername);
}
