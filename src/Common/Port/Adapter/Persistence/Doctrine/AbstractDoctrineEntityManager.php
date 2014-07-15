<?php

namespace SaasOvation\Common\Port\Adapter\Persistence\Doctrine;

use Doctrine\ORM\EntityManager;

abstract class AbstractDoctrineEntityManager
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $anEntityManager)
    {
        $this->setEntityManager($anEntityManager);
    }

    protected function entityManager()
    {
        return $this->entityManager;
    }

    private function setEntityManager(EntityManager $anEntityManager)
    {
        $this->entityManager = $anEntityManager;
    }
}
