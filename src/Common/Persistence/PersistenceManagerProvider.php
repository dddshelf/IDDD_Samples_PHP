<?php

namespace SaasOvation\Common\Persistence;

use Doctrine\ORM\EntityManager;

class PersistenceManagerProvider
{
    /**
     * @var EntityManager
     */
    private $doctrineEntityManager;

    public function __construct(EntityManager $aDoctrineEntityManager)
    {
        $this->setHibernateSession($aDoctrineEntityManager);
    }

    public function doctrineEntityManager()
    {
        return $this->doctrineEntityManager;
    }

    public function hasDoctrineEntityManager()
    {
        return null !== $this->doctrineEntityManager();
    }

    private function setHibernateSession(EntityManager $aDoctrineEntityManager)
    {
        $this->doctrineEntityManager = $aDoctrineEntityManager;
    }
}
