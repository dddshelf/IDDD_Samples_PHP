<?php

namespace SaasOvation\IdentityAccess\Test\Infrastructure\Persistence;

use Doctrine\Common\Collections\ArrayCollection;
use LogicException;
use PhpCollection\Map;
use SaasOvation\Common\Persistence\CleanableStore;
use SaasOvation\IdentityAccess\Domain\Model\Access\Role;
use SaasOvation\IdentityAccess\Domain\Model\Access\RoleRepository;
use SaasOvation\IdentityAccess\Domain\Model\Identity\TenantId;

class InMemoryRoleRepository implements RoleRepository, CleanableStore
{
    /**
     * @var Map
     */
    private $repository;

    public function __construct()
    {
        $this->repository = new Map();
    }

    public function add(Role $aRole)
    {
        $key = $this->keyOfRole($aRole);

        if ($this->repository()->containsKey($key)) {
            throw new LogicException('Duplicate key.');
        }

        $this->repository()->set($key, $aRole);
    }

    public function allRoles(TenantId $aTenantId)
    {
        $roles = new ArrayCollection();

        foreach ($this->repository()->values() as $role) {
            if ($role->tenantId()->equals($aTenantId)) {
                $roles->add($role);
            }
        }

        return $roles;
    }

    public function remove(Role $aRole)
    {
        $key = $this->keyOfRole($aRole);

        $this->repository()->remove($key);
    }

    public function roleNamed(TenantId $aTenantId, $aRoleName)
    {
        return $this->repository()->get($this->keyOfTenant($aTenantId, $aRoleName))->getOrElse(null);
    }

    public function clean()
    {
        $this->repository()->clear();
    }

    private function keyOfTenant(TenantId $aTenantId, $aRoleName)
    {
        $key = $aTenantId->id() . '#' . $aRoleName;

        return $key;
    }

    private function keyOfRole(Role $aRole)
    {
        return $this->keyOfTenant($aRole->tenantId(), $aRole->name());
    }

    private function repository()
    {
        return $this->repository;
    }
}
