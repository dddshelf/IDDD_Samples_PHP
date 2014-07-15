<?php

namespace SaasOvation\Common\Port\Adapter\Persistence\Doctrine;

use DateTimeImmutable;
use Exception;
use SaasOvation\Common\Domain\Model\Process\ProcessId;
use SaasOvation\Common\Domain\Model\Process\TimeConstrainedProcessTracker;
use SaasOvation\Common\Domain\Model\Process\TimeConstrainedProcessTrackerRepository;

class DoctrineTimeConstrainedProcessTrackerRepository
    extends AbstractDoctrineEntityManager
    implements TimeConstrainedProcessTrackerRepository
{
    public function add(TimeConstrainedProcessTracker $aTimeConstrainedProcessTracker)
    {
        $this->save($aTimeConstrainedProcessTracker);
    }

    public function allTimedOut()
    {
        $query = $this->entityManager()->createQuery(
            'SELECT tcpt
             FROM SaasOvation\Common\Domain\Model\Process\TimeConstrainedProcessTracker tcpt
             WHERE tcpt.completed = FALSE
               AND tcpt.processInformedOfTimeout = FALSE
               AND tcp.timeoutOccursOn <= ?1'
        );

        $query->setParameter(1, (new DateTimeImmutable())->getTimestamp());

        return $query->execute();
    }

    public function allTimedOutOf($aTenantId)
    {
        $query = $this->entityManager()->createQuery(
            'SELECT tcpt
             FROM SaasOvation\Common\Domain\Model\Process\TimeConstrainedProcessTracker tcpt
             WHERE tcpt.tenantId = ?1
               AND tcpt.completed = FALSE
               AND tcpt.processInformedOfTimeout = FALSE
               AND tcp.timeoutOccursOn <= ?2'
        );

        $query->setParameter(1, $aTenantId);
        $query->setParameter(2, (new DateTimeImmutable())->getTimestamp());

        return $query->execute();
    }

    public function allTrackers($aTenantId)
    {
        $query = $this->entityManager()->createQuery(
            'SELECT tcpt
             FROM SaasOvation\Common\Domain\Model\Process\TimeConstrainedProcessTracker tcpt
             WHERE tcpt.tenantId = ?1'
        );

        $query->setParameter(1, $aTenantId);

        return $query->execute();
    }

    public function save(TimeConstrainedProcessTracker $aTimeConstrainedProcessTracker)
    {
        try {
            $this->entityManager()->persist($aTimeConstrainedProcessTracker);
            $this->entityManager()->flush($aTimeConstrainedProcessTracker);
        } catch (Exception $e) {
            throw new Exception('Either TimeConstrainedProcessTracker is not unique or another constraint has been violated.', $e->getCode(), $e);
        }
    }

    public function trackerOfProcessId($aTenantId, ProcessId $aProcessId)
    {
        $query = $this->entityManager()->createQuery(
            'SELECT tcpt
             FROM SaasOvation\Common\Domain\Model\Process\TimeConstrainedProcessTracker tcpt
             WHERE tcpt.tenantId = ?1
               AND tcpt.processId = ?2'
        );

        $query->setParameter(1, $aTenantId);
        $query->setParameter(2, $aProcessId);

        return $query->execute();
    }
}
