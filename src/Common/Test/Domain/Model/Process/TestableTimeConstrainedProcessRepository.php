<?php

namespace SaasOvation\Common\Test\Domain\Model\Process;

use SaasOvation\Common\Domain\Model\Process\ProcessId;
use SaasOvation\Common\Port\Adapter\Persistence\Doctrine\AbstractDoctrineEntityManager;

class TestableTimeConstrainedProcessRepository extends AbstractDoctrineEntityManager
{
    public function add(TestableTimeConstrainedProcess $aTestableTimeConstrainedProcess)
    {
        $this->entityManager()->persist($aTestableTimeConstrainedProcess);
        $this->entityManager()->flush($aTestableTimeConstrainedProcess);
    }

    public function processOfId(ProcessId $aProcessId)
    {
        $query = $this->entityManager()->createQuery(
            'SELECT ttcp
             FROM SaasOvation\Common\Test\Domain\Model\Process\TestableTimeConstrainedProcess ttcp
             WHERE ttcp.processId = ?1'
        );

        $query->setParameter(1, $aProcessId);

        return $query->getSingleResult();
    }

    public function getEntityManager()
    {
        return $this->entityManager();
    }
}
