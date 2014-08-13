<?php

namespace SaasOvation\IdentityAccess\Test\Infrastructure\Persistence;

use Doctrine\Common\Collections\ArrayCollection;
use LogicException;
use PhpCollection\Map;
use Rhumsaa\Uuid\Uuid;
use SaasOvation\Common\Persistence\CleanableStore;
use SaasOvation\IdentityAccess\Domain\Model\Identity\Tenant;
use SaasOvation\IdentityAccess\Domain\Model\Identity\TenantId;
use SaasOvation\IdentityAccess\Domain\Model\Identity\TenantRepository;

class InMemoryTenantRepository implements TenantRepository, CleanableStore
{
    /**
     * @var Map
     */
    private $repository;

    public function __construct()
    {
        $this->repository = new ArrayCollection();
    }

    public function add(Tenant $aTenant)
    {
        $key = $this->keyOfTenant($aTenant);

        if ($this->repository()->containsKey($key)) {
            throw new LogicException('Duplicate key.');
        }

        $this->repository()->set($key, $aTenant);
    }

    public function nextIdentity()
    {
        return new TenantId(strtoupper(Uuid::uuid4()));
    }

    public function tenantNamed($aName)
    {
        foreach ($this->repository()->values() as $tenant) {
            if ($tenant->name()->equals($aName)) {
                return $tenant;
            }
        }
    }

    public function tenantOfId(TenantId $aTenantId)
    {
        return $this->repository()->get($this->keyOf($aTenantId));
    }

    public function remove(Tenant $aTenant)
    {
        $key = $this->keyOf($aTenant);

        $this->repository()->remove($key);
    }

    public function clean()
    {
        $this->repository()->clear();
    }

    private function keyOf(TenantId $aTenantId)
    {
        return $aTenantId->id();
    }

    private function keyOfTenant(Tenant $aTenant)
    {
        return $this->keyOf($aTenant->tenantId());
    }

    private function repository()
    {
        return $this->repository;
    }
}
