<?php

namespace SaasOvation\Common\Domain\Model\Process;

use DateTime;
use DateTimeInterface;
use SaasOvation\Common\Domain\Model\Entity;

abstract class AbstractProcess extends Entity implements Process
{
    /**
     * @var int
     */
    private $allowableDuration;

    /**
     * @var int
     */
    private $concurrencyVersion;

    /**
     * @var string
     */
    private $description;

    /**
     * @var ProcessId
     */
    private $processId;

    /**
     * @var ProcessCompletionType
     */
    private $processCompletionType;

    /**
     * @var DateTimeInterface
     */
    private $startTime;

    /**
     * @var string
     */
    private $tenantId;

    /**
     * @var DateTimeInterface
     */
    private $timedOutDate;

    /**
     * @var int
     */
    private $totalRetriesPermitted;

    public function __construct($aTenantId, ProcessId $aProcessId, $aDescription, $anAllowableDuration = null, $aTotalRetriesPermitted = null)
    {
        $this->setDescription($aDescription);
        $this->setProcessCompletionType(ProcessCompletionType::NotCompleted());
        $this->setProcessId($aProcessId);
        $this->setStartTime(new DateTime());
        $this->setTenantId($aTenantId);

        if (null !== $anAllowableDuration) {
            $this->setAllowableDuration($anAllowableDuration);
        }

        if (null !== $aTotalRetriesPermitted) {
            $this->setTotalRetriesPermitted($aTotalRetriesPermitted);
        }
    }

    public function allowableDuration()
    {
        return $this->allowableDuration;
    }

    public function canTimeout() {
        return $this->allowableDuration() > 0;
    }

    public function currentDuration() {
        return $this->calculateTotalCurrentDuration(new DateTime());
    }

    public function description()
    {
        return $this->description;
    }

    public function didProcessingComplete()
    {
        return $this->isCompleted() && !$this->isTimedOut();
    }

    public function failWhenConcurrencyViolation($aVersion)
    {
        $this->assertStateTrue(
            $aVersion == $this->concurrencyVersion(),
            'Concurrency Violation: Stale data detected. Entity was already modified.'
        );
    }

    public function informTimeout(DateTimeInterface $aTimedOutDate)
    {
        $this->assertStateTrue(
            $this->hasProcessTimedOut($aTimedOutDate),
            'The date ' . $aTimedOutDate->format('Y-m-d H:i:s') . ' does not indicate a valid timeout.'
        );

        $this->setProcessCompletionType(ProcessCompletionType::TimedOut());
        $this->setTimedOutDate($aTimedOutDate);
    }

    public function isCompleted()
    {
        return !$this->notCompleted();
    }

    public function isTimedOut()
    {
        return null !== $this->timedOutDate();
    }

    public function notCompleted()
    {
        return $this->processCompletionType()->is(ProcessCompletionType::NotCompleted);
    }

    public function processCompletionType()
    {
        return $this->processCompletionType;
    }

    public function processId()
    {
        return $this->processId;
    }

    public function startTime()
    {
        return $this->startTime;
    }

    public function tenantId()
    {
        return $this->tenantId;
    }

    public function timeConstrainedProcessTracker()
    {
        $this->assertStateTrue($this->canTimeout(), 'Process does not timeout.');

        $tracker = new TimeConstrainedProcessTracker(
            $this->tenantId(),
            $this->processId(),
            $this->description(),
            $this->startTime(),
            $this->allowableDuration(),
            $this->totalRetriesPermitted(),
            $this->processTimedOutEventType()
        );

        return $tracker;
    }

    public function timedOutDate()
    {
        return $this->timedOutDate;
    }

    public function totalAllowableDuration()
    {
        $totalAllowableDuration = $this->allowableDuration();
        $totalRetriesPermitted = $this->totalRetriesPermitted();

        if ($totalRetriesPermitted > 0) {
            $totalAllowableDuration *= $totalRetriesPermitted;
        }

        return $totalAllowableDuration;
    }

    public function totalRetriesPermitted()
    {
        return $this->totalRetriesPermitted;
    }

    public function equals($anObject)
    {
        $equalObjects = false;

        if (null !== $anObject && get_class($this) === get_class($anObject)) {
            $equalObjects =
                $anObject->tenantId() === $this->tenantId()
                && $this->processId()->equals($anObject->processId());
        }

        return $equalObjects;
    }

    public function __toString()
    {
        return 'AbstractProcess [id=' . $this->id() . 'allowableDuration=' . $this->allowableDuration
        . ', description=' . $this->description . ', processId=' . $this->processId
        . ', processCompletionType=' . $this->processCompletionType . ', startTime=' . $this->startTime
        . ', tenantId=' . $this->tenantId . ', timedOutDate=' . $this->timedOutDate
        . ', totalRetriesPermitted=' . $this->totalRetriesPermitted . ']';
    }

    protected function completeProcess(ProcessCompletionType $aProcessCompletionType)
    {
        if (!$this->isCompleted() && $this->completenessVerified()) {
            $this->setProcessCompletionType($aProcessCompletionType);
        }
    }

    protected function concurrencyVersion()
    {
        return $this->concurrencyVersion;
    }

    protected function setConcurrencyVersion($aConcurrencyVersion)
    {
        $this->concurrencyVersion = $aConcurrencyVersion;
    }

    protected abstract function completenessVerified();

    protected abstract function processTimedOutEventType();

    private function calculateTotalCurrentDuration(DateTimeInterface $aDateFollowingStartTime)
    {
        return $aDateFollowingStartTime->getTimestamp() - $this->startTime()->getTimestamp();
    }

    private function hasProcessTimedOut(DateTimeInterface $aTimedOutDate)
    {
        return $this->calculateTotalCurrentDuration($aTimedOutDate) >= $this->totalAllowableDuration();
    }

    private function setAllowableDuration($anAllowableDuration)
    {
        $this->assertArgumentTrue(
            $anAllowableDuration > 0,
            'The allowable duration must be greater than zero.'
        );

        $this->allowableDuration = $anAllowableDuration;
    }

    private function setDescription($description)
    {
        $this->description = $description;
    }

    private function setProcessCompletionType(ProcessCompletionType $aProcessCompletionType)
    {
        $this->processCompletionType = $aProcessCompletionType;
    }

    private function setProcessId(ProcessId $aProcessId)
    {
        $this->assertArgumentNotNull($aProcessId, 'Process id must be provided.');

        $this->processId = $aProcessId;
    }

    private function setStartTime(DateTimeInterface $aStartTime)
    {
        $this->startTime = $aStartTime;
    }

    private function setTenantId($aTenantId)
    {
        $this->assertArgumentNotEmpty($aTenantId, 'Tenant id must be provided.');

        $this->tenantId = $aTenantId;
    }

    private function setTimedOutDate(DateTimeInterface $aTimedOutDate)
    {
        $this->timedOutDate = $aTimedOutDate;
    }

    private function setTotalRetriesPermitted($aTotalRetriesPermitted)
    {
        $this->totalRetriesPermitted = $aTotalRetriesPermitted;
    }
}
