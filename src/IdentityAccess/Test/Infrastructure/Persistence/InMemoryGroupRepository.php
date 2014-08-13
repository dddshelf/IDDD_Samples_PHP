<?php

namespace SaasOvation\IdentityAccess\Test\Infrastructure\Persistence;

use Doctrine\Common\Collections\ArrayCollection;
use InvalidArgumentException;
use PhpCollection\Map;
use SaasOvation\Common\Persistence\CleanableStore;
use SaasOvation\IdentityAccess\Domain\Model\Identity\Group;
use SaasOvation\IdentityAccess\Domain\Model\Identity\GroupRepository;
use SaasOvation\IdentityAccess\Domain\Model\Identity\TenantId;

class InMemoryGroupRepository implements GroupRepository, CleanableStore
{
    /**
     * @var Map
     */
    private $repository;

    public function __construct()
    {
        $this->repository = new Map();
    }

    public function add(Group $aGroup)
    {
        $key = $this->keyOfGroup($aGroup);

        if ($this->repository()->containsKey($key)) {
            throw new InvalidArgumentException('Duplicate key.');
        }

        $this->repository()->set($key, $aGroup);
    }

    public function allGroups(TenantId $aTenantId)
    {
        $groups = new ArrayCollection();

        foreach ($this->repository()->values() as $group) {
            if ($group->tenantId()->equals($aTenantId)) {
                $groups->add($group);
            }
        }

        return $groups;
    }

    public function groupNamed(TenantId $aTenantId, $aName)
    {
        if (0 === strpos($aName, Group::$ROLE_GROUP_PREFIX)) {
            throw new InvalidArgumentException('May not find internal groups.');
        }

        $key = $this->keyOfTenant($aTenantId, $aName);

        return $this->repository()->get($key)->getOrElse(null);
    }

    public function remove(Group $aGroup)
    {
        $key = $this->keyOfGroup($aGroup);

        $this->repository()->remove($key);
    }

    public function clean()
    {
        $this->repository()->clear();
    }

    private function keyOfTenant(TenantId $aTenantId, $aName)
    {
        return $aTenantId->id() . '#' . $aName;
    }

    private function keyOfGroup(Group $aGroup)
    {
        return $this->keyOfTenant($aGroup->tenantId(), $aGroup->name());
    }

    private function repository()
    {
        return $this->repository;
    }
}
