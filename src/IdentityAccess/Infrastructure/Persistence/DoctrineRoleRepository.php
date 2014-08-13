<?php

namespace SaasOvation\IdentityAccess\Infrastructure\Persistence;

use Exception;
use LogicException;
use SaasOvation\Common\Port\Adapter\Persistence\Doctrine\AbstractDoctrineEntityManager;
use SaasOvation\IdentityAccess\Domain\Model\Access\Role;
use SaasOvation\IdentityAccess\Domain\Model\Access\RoleRepository;
use SaasOvation\IdentityAccess\Domain\Model\Identity\TenantId;

class DoctrineRoleRepository
    extends AbstractDoctrineEntityManager
    implements RoleRepository
{
    public function add(Role $aRole)
    {
        try {
            $this->entityManager()->persist($aRole);
        } catch (Exception $e) {
            throw new LogicException('Role is not unique.', $e->getCode(), $e);
        }
    }

    public function allRoles(TenantId $aTenantId)
    {
        $query = $this->entityManager()->createQuery(
            'SELECT r
             FROM SaasOvation\IdentityAccess\Domain\Model\Access\Role r
             WHERE tenantId = ?1'
        );

        $query->setParameter(1, $aTenantId);

        return $query->execute();
    }

    public function remove(Role $aRole)
    {
        $this->entityManager()->remove($aRole);
    }

    public function roleNamed(TenantId $aTenantId, $aRoleName)
    {
        $query = $this->entityManager()->createQuery(
            'SELECT r
             FROM SaasOvation\IdentityAccess\Domain\Model\Access\Role r
             WHERE tenantId = ?1
               AND name = ?2'
        );

        $query->setParameter(1, $aTenantId);
        $query->setParameter(2, $aRoleName);

        return $query->getSingleResult();
    }
}
