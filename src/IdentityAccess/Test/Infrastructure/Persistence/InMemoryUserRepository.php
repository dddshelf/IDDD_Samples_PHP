<?php

namespace SaasOvation\IdentityAccess\Test\Infrastructure\Persistence;

use Doctrine\Common\Collections\ArrayCollection;
use LogicException;
use PhpCollection\Map;
use SaasOvation\Common\Persistence\CleanableStore;
use SaasOvation\IdentityAccess\Domain\Model\Identity\TenantId;
use SaasOvation\IdentityAccess\Domain\Model\Identity\User;
use SaasOvation\IdentityAccess\Domain\Model\Identity\UserRepository;

class InMemoryUserRepository implements UserRepository, CleanableStore
{
    /**
     * @var Map
     */
    private $repository;

    public function __construct()
    {
        $this->repository = new Map();
    }

    public function add(User $aUser)
    {
        $key = $this->keyOfUser($aUser);

        if ($this->repository()->containsKey($key)) {
            throw new LogicException('Duplicate key.');
        }

        $this->repository()->set($key, $aUser);
    }

    public function allSimilarlyNamedUsers(TenantId $aTenantId, $aFirstNamePrefix, $aLastNamePrefix)
    {
        $users = new ArrayCollection();

        $aFirstNamePrefix = strtolower($aFirstNamePrefix);
        $aLastNamePrefix = strtolower($aLastNamePrefix);

        foreach ($this->repository()->values() as $user) {
            if ($user->tenantId()->equals($aTenantId)) {
                $name = $user->person()->name();
                if ($aFirstNamePrefix === substr(strtolower($name->firstName()), 0, strlen($aFirstNamePrefix))
                    && $aLastNamePrefix === substr(strtolower($name->lastName()), 0, strlen($aLastNamePrefix))
                ) {
                    $users->add($user);
                }
            }
        }

        return $users;
    }

    public function remove(User $aUser)
    {
        $key = $this->keyOfUser($aUser);

        $this->repository()->remove($key);
    }

    public function userFromAuthenticCredentials(TenantId $aTenantId, $aUsername, $anEncryptedPassword)
    {
        foreach ($this->repository()->values() as $user) {
            if ($user->tenantId()->equals($aTenantId)) {
                if ($user->username() === $aUsername) {
                    if ($user->internalAccessOnlyEncryptedPassword() === $anEncryptedPassword) {
                        return $user;
                    }
                }
            }
        }

        return null;
    }

    public function userWithUsername(TenantId $aTenantId, $aUsername)
    {
        foreach ($this->repository()->values() as $user) {
            if ($user->tenantId()->equals($aTenantId)
                && $user->username() === $aUsername
            ) {
                return $user;
            }
        }
    }

    public function clean()
    {
        $this->repository()->clear();
    }

    private function keyOf(TenantId $aTenantId, $aUsername)
    {
        return $aTenantId->id() . '#' . $aUsername;
    }

    private function keyOfUser(User $aUser)
    {
        return $this->keyOf($aUser->tenantId(), $aUser->username());
    }

    private function repository()
    {
        return $this->repository;
    }
}
