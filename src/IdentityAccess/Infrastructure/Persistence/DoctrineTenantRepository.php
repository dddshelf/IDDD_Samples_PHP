<?php

namespace SaasOvation\IdentityAccess\Infrastructure\Persistence;

use Exception;
use LogicException;
use Rhumsaa\Uuid\Uuid;
use SaasOvation\Common\Port\Adapter\Persistence\Doctrine\AbstractDoctrineEntityManager;
use SaasOvation\IdentityAccess\Domain\Model\Identity\Tenant;
use SaasOvation\IdentityAccess\Domain\Model\Identity\TenantId;
use SaasOvation\IdentityAccess\Domain\Model\Identity\TenantRepository;

class DoctrineTenantRepository
    extends AbstractDoctrineEntityManager
    implements TenantRepository
{
    public function add(Tenant $aTenant)
    {
        try {
            $this->entityManager()->persist($aTenant);
        } catch (Exception $e) {
            throw new LogicException('Tenant is not unique.', $e->getCode(), $e);
        }
    }

    public function nextIdentity()
    {
        return new TenantId(
            strtoupper(Uuid::uuid4()->toString())
        );
    }

    public function remove(Tenant $aTenant)
    {
        $this->entityManager()->remove($aTenant);
    }

    public function tenantNamed($aName)
    {
        $query = $this->entityManager()->createQuery(
            'SELECT t
             FROM SaasOvation\IdentityAccess\Domain\Model\Identity\Tenant t
             WHERE name = ?1'
        );

        $query->setParameter(1, $aName);

        return $query->getSingleResult();
    }

    public function tenantOfId(TenantId $aTenantId)
    {
        $query = $this->entityManager()->createQuery(
            'SELECT t
             FROM SaasOvation\IdentityAccess\Domain\Model\Identity\Tenant t
             WHERE tenantId = ?1'
        );

        $query->setParameter(1, $aTenantId);

        return $query->getSingleResult();
    }
}
