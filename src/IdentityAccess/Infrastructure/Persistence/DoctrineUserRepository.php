<?php

namespace SaasOvation\IdentityAccess\Infrastructure\Persistence;

use Exception;
use LogicException;
use SaasOvation\Common\Port\Adapter\Persistence\Doctrine\AbstractDoctrineEntityManager;
use SaasOvation\IdentityAccess\Domain\Model\Identity\TenantId;
use SaasOvation\IdentityAccess\Domain\Model\Identity\User;
use SaasOvation\IdentityAccess\Domain\Model\Identity\UserRepository;

class HibernateUserRepository
    extends AbstractDoctrineEntityManager
    implements UserRepository
{
    public function add(User $aUser)
    {
        try {
            $this->entityManager()->persist($aUser);
        } catch (Exception $e) {
            throw new LogicException('User is not unique.', $e->getCode(), $e);
        }
    }

    public function allSimilarlyNamedUsers(TenantId $aTenantId, $aFirstNamePrefix, $aLastNamePrefix)
    {
        if ('%' === substr($aFirstNamePrefix, -1) || '%' === substr($aLastNamePrefix, -1)) {
            throw new LogicException('Name prefixes must not include %.');
        }

        $query = $this->entityManager()->createQuery(
            'SELECT u
             FROM SaasOvation\IdentityAccess\Domain\Model\Identity\User u
             WHERE tenantId = ?1
               AND u.person.name.firstName LIKE ?2
               AND u.person.name.lastName LIKE ?3'
        );

        $query->setParameter(1, $aTenantId);
        $query->setParameter(2, $aFirstNamePrefix . '%');
        $query->setParameter(3, $aLastNamePrefix . '%');

        return $query->execute();
    }

    public function remove(User $aUser)
    {
        $this->entityManager()->remove($aUser);
    }

    public function userFromAuthenticCredentials(TenantId $aTenantId, $aUsername, $anEncryptedPassword)
    {
        $query = $this->entityManager()->createQuery(
            'SELECT u
             FROM SaasOvation\IdentityAccess\Domain\Model\Identity\User u
             WHERE tenantId = ?1
               AND u.username = ?2
               AND u.password = ?3'
        );

        $query->setParameter(1, $aTenantId);
        $query->setParameter(2, $aUsername);
        $query->setParameter(3, $anEncryptedPassword);

        return $query->getSingleResult();
    }

    public function userWithUsername(TenantId $aTenantId, $aUsername)
    {
        $query = $this->entityManager()->createQuery(
            'SELECT u
             FROM SaasOvation\IdentityAccess\Domain\Model\Identity\User u
             WHERE tenantId = ?1
               AND u.username = ?2'
        );

        $query->setParameter(0, $aTenantId);
        $query->setParameter(1, $aUsername);

        return $query->getSingleResult();
    }
}
