<?php

namespace SaasOvation\IdentityAccess\Infrastructure\Persistence;

use Exception;
use InvalidArgumentException;
use LogicException;
use SaasOvation\Common\Port\Adapter\Persistence\Doctrine\AbstractDoctrineEntityManager;
use SaasOvation\IdentityAccess\Domain\Model\Identity\Group;
use SaasOvation\IdentityAccess\Domain\Model\Identity\GroupRepository;
use SaasOvation\IdentityAccess\Domain\Model\Identity\TenantId;

class DoctrineGroupRepository
    extends AbstractDoctrineEntityManager
    implements GroupRepository
{
    public function add(Group $aGroup)
    {
        try {
            $this->entityManager()->persist($aGroup);
        } catch (Exception $e) {
            throw new LogicException('Group is not unique.', $e);
        }
    }

    public function allGroups(TenantId $aTenantId)
    {
        $query = $this->entityManager()->createQuery(
            "SELECT g
             FROM SaasOvation\\IdentityAccess\\Domain\\Model\\Identity\\Group g
             WHERE tenantId = ?1
               AND name NOT LIKE '" . Group::$ROLE_GROUP_PREFIX . "%'"
        );

        $query->setParameter(1, $aTenantId);

        return $query->execute();
    }

    public function groupNamed(TenantId $aTenantId, $aName)
    {
        if (0 === strpos($aName, Group::$ROLE_GROUP_PREFIX)) {
            throw new InvalidArgumentException('May not find internal groups.');
        }

        $query = $this->entityManager()->createQuery(
            "SELECT g
             FROM SaasOvation\\IdentityAccess\\Domain\\Model\\Identity\\Group g
             WHERE tenantId = ?1
               AND name = ?2"
        );

        $query->setParameter(1, $aTenantId);
        $query->setParameter(2, $aName);

        return $query->getSingleResult();
    }

    public function remove(Group $aGroup)
    {
        $this->entityManager()->remove($aGroup);
    }
}
