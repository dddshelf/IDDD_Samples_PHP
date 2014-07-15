<?php

namespace SaasOvation\Common\Port\Adapter\Persistence\LevelDB;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use SaasOvation\Common\Domain\Model\Process\ProcessId;
use SaasOvation\Common\Domain\Model\Process\TimeConstrainedProcessTracker;
use SaasOvation\Common\Domain\Model\Process\TimeConstrainedProcessTrackerRepository;

class LevelDBTimeConstrainedProcessTrackerRepository
    extends AbstractLevelDBRepository
    implements TimeConstrainedProcessTrackerRepository
{
    private static $PRIMARY                 = 'TCPROC_TRACKER#PK';
    private static $ALL_TRACKERS            = 'TCPROC_TRACKER#ALL';
    private static $ALL_TENANT_TRACKERS     = 'TCPROC_TRACKER#TENANT';

    public function add(TimeConstrainedProcessTracker $aTimeConstrainedProcessTracker)
    {
        $this->save($aTimeConstrainedProcessTracker);
    }

    public function allTimedOut()
    {
        $trackers = $this->listAllTrackers();

        $this->filterTimedOut($trackers);

        return $trackers;
    }

    public function allTimedOutOf($aTenantId)
    {
        $trackers = $this->listAllTrackers($aTenantId);

        $this->filterTimedOut($trackers);

        return $trackers;
    }

    public function allTrackers($aTenantId)
    {
        return $this->listAllTrackers($aTenantId);
    }

    public function save(TimeConstrainedProcessTracker $aTimeConstrainedProcessTracker)
    {
        $uow = LevelDBUnitOfWork::start($this->database());

        $this->doSave($aTimeConstrainedProcessTracker, $uow);
    }

    public function trackerOfProcessId($aTenantId, ProcessId $aProcessId)
    {
        $primaryKey = LevelDBKey::createFromCategoryAndSegments(static::$PRIMARY, $aTenantId, $aProcessId->id());

        $tracker = LevelDBUnitOfWork::readOnly($this->database())->readObjectFromString(
            $primaryKey->key(),
            TimeConstrainedProcessTracker::class
        );

        return $tracker;
    }

    private function filterTimedOut(Collection $trackers)
    {
        $now = new DateTimeImmutable();

        foreach ($trackers as $tracker) {
            if (null !== $tracker) {
                $timeout = (new DateTimeImmutable())->setTimestamp($tracker->timeoutOccursOn());

                if ($timeout > $now) {
                    $trackers->removeElement($tracker);
                }
            }
        }
    }

    private function listAllTrackers($aTenantId = null)
    {
        $allTrackers = new ArrayCollection();

        $levelDbKeyArguments = [
            static::$ALL_TRACKERS
        ];

        if (null !== $aTenantId) {
            $levelDbKeyArguments = [
                static::$ALL_TENANT_TRACKERS,
                $aTenantId
            ];
        }

        $allTrackerKey = call_user_func_array([__NAMESPACE__ . '\\LevelDBKey', 'createFromCategoryAndSegments'], $levelDbKeyArguments);

        $uow = LevelDBUnitOfWork::readOnly($this->database());

        $keys = $uow->readKeys($allTrackerKey);

        foreach ($keys as $trackerId) {
            $tracker = $uow->readObjectFromString(
                $trackerId,
                TimeConstrainedProcessTracker::class
            );

            if (null !== $tracker) {
                $allTrackers->add($tracker);
            }
        }

        return $allTrackers;
    }

    private function doSave(TimeConstrainedProcessTracker $aTimeConstrainedProcessTracker, LevelDBUnitOfWork $aUoW)
    {
        $primaryKey = LevelDBKey::createFromCategoryAndSegments(
            static::$PRIMARY,
            $aTimeConstrainedProcessTracker->tenantId(),
            $aTimeConstrainedProcessTracker->processId()->id()
        );

        $aUoW->write($primaryKey, $aTimeConstrainedProcessTracker);

        $allTrackers = LevelDBKey::createFromPrimaryKey($primaryKey, static::$ALL_TRACKERS);

        $aUoW->updateKeyReference($allTrackers);

        $allTenantTrackers = LevelDBKey::createFromPrimaryKey(
            $primaryKey,
            static::$ALL_TENANT_TRACKERS,
            $aTimeConstrainedProcessTracker->tenantId()
        );

        $aUoW->updateKeyReference($allTenantTrackers);
    }
}
