<?php

namespace SaasOvation\Common\Domain\Model\Process;

use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use ReflectionClass;
use RuntimeException;
use SaasOvation\Common\AssertionConcern;
use SaasOvation\Common\Domain\Model\DomainEventPublisher;

class TimeConstrainedProcessTracker extends AssertionConcern
{
    /**
     * @var int
     */
    private $allowableDuration;

    /**
     * @var boolean
     */
    private $completed;

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
     * @var boolean
     */
    private $processInformedOfTimeout;

    /**
     * @var string
     */
    private $processTimedOutEventType;

    /**
     * @var int
     */
    private $retryCount;

    /**
     * @var string
     */
    private $tenantId;

    /**
     * @var int
     */
    private $timeConstrainedProcessTrackerId;

    /**
     * @var int
     */
    private $timeoutOccursOn;

    /**
     * @var int
     */
    private $totalRetriesPermitted;

    public function __construct(
        $aTenantId,
        ProcessId $aProcessId,
        $aDescription,
        DateTimeInterface $anOriginalStartTime,
        $anAllowableDuration,
        $aTotalRetriesPermitted,
        $aProcessTimedOutEventType
    ) {
        $this->setAllowableDuration($anAllowableDuration);
        $this->setDescription($aDescription);
        $this->setProcessId($aProcessId);
        $this->setProcessTimedOutEventType($aProcessTimedOutEventType);
        $this->setTenantId($aTenantId);
        $this->setTimeConstrainedProcessTrackerId(-1);
        $this->setTimeoutOccursOn($anOriginalStartTime->getTimestamp() + $anAllowableDuration);
        $this->setTotalRetriesPermitted($aTotalRetriesPermitted);
    }

    public function allowableDuration()
    {
        return $this->allowableDuration;
    }

    public function completed()
    {
        $this->completed = true;
    }

    public function isCompleted()
    {
        return $this->completed;
    }

    public function description()
    {
        return $this->description;
    }

    public function failWhenConcurrencyViolation($aVersion)
    {
        $this->assertStateTrue(
            $aVersion === $this->concurrencyVersion(),
            'Concurrency Violation: Stale data detected. Entity was already modified.'
        );
    }

    public function processId()
    {
        return $this->processId;
    }

    public function isProcessInformedOfTimeout()
    {
        return $this->processInformedOfTimeout;
    }

    public function processTimedOutEventType()
    {
        return $this->processTimedOutEventType;
    }

    public function hasTimedOut()
    {
        $timeout = (new DateTimeImmutable())->setTimestamp($this->timeoutOccursOn());
        $now = new DateTimeImmutable();

        return ($timeout == $now || $timeout < $now);
    }

    public function informProcessTimedOut()
    {
        if (!$this->isProcessInformedOfTimeout() && $this->hasTimedOut()) {

            $processTimedOut = null;

            if ($this->totalRetriesPermitted() == 0) {
                $processTimedOut = $this->processTimedOutEvent();

                $this->setProcessInformedOfTimeout(true);
            } else {
                $this->incrementRetryCount();

                $processTimedOut = $this->processTimedOutEventWithRetries();

                if ($this->totalRetriesReached()) {
                    $this->setProcessInformedOfTimeout(true);
                } else {
                    $this->setTimeoutOccursOn(
                        $this->timeoutOccursOn()
                        + $this->allowableDuration()
                    );
                }
            }

            DomainEventPublisher::instance()->publish($processTimedOut);
        }
    }

    public function retryCount()
    {
        return $this->retryCount;
    }

    public function tenantId()
    {
        return $this->tenantId;
    }

    public function timeConstrainedProcessTrackerId()
    {
        return $this->timeConstrainedProcessTrackerId;
    }

    public function timeoutOccursOn()
    {
        return $this->timeoutOccursOn;
    }

    public function totalRetriesPermitted()
    {
        return $this->totalRetriesPermitted;
    }

    public function equals($anObject)
    {
        $equalObjects = false;

        if (null !== $anObject && get_class($this) == get_class($anObject)) {
            $equalObjects = $this->tenantId() == $anObject->tenantId()
                && $this->processId()->equals($anObject->processId());
        }

        return $equalObjects;
    }

    public function __toString()
    {
        return 'TimeConstrainedProcessTracker [allowableDuration=' . $this->allowableDuration . ', completed=' . $this->completed
        . ', description=' . $this->description . ', processId=' . $this->processId . ', processInformedOfTimeout='
        . $this->processInformedOfTimeout . ', processTimedOutEventType=' . $this->processTimedOutEventType . ', retryCount='
        . $this->retryCount . ', tenantId=' . $this->tenantId . ', timeConstrainedProcessTrackerId=' . $this->timeConstrainedProcessTrackerId
        . ', timeoutOccursOn=' . $this->timeoutOccursOn . ', totalRetriesPermitted=' . $this->totalRetriesPermitted . ']';
    }

    protected function concurrencyVersion()
    {
        return $this->concurrencyVersion;
    }

    protected function setConcurrencyVersion($aConcurrencyVersion)
    {
        $this->concurrencyVersion = $aConcurrencyVersion;
    }

    private function incrementRetryCount()
    {
        $this->retryCount++;
    }

    private function setAllowableDuration($anAllowableDuration)
    {
        $this->assertArgumentTrue($anAllowableDuration > 0, 'The allowable duration must be greater than zero.');

        $this->allowableDuration = $anAllowableDuration;
    }

    private function setDescription($aDescription)
    {
        $this->assertArgumentNotEmpty($aDescription, 'Description is required.');
        $this->assertArgumentLength($aDescription, 1, 100, 'Description must be 1 to 100 characters in length.');

        $this->description = $aDescription;
    }

    private function setProcessInformedOfTimeout($isProcessInformedOfTimeout)
    {
        $this->processInformedOfTimeout = $isProcessInformedOfTimeout;
    }

    private function processTimedOutEvent()
    {
        $processTimedOut = null;

        try {
            $reflectedClass = new ReflectionClass($this->processTimedOutEventType());

            $processTimedOut = $reflectedClass->newInstance($this->processId());

        } catch (Exception $e) {
            throw new RuntimeException('Error creating new ProcessTimedOut instance because: ' . $e->getMessage());
        }

        return $processTimedOut;
    }

    private function processTimedOutEventWithRetries()
    {
        $processTimedOut = null;

        try {
            $reflectedClass = new ReflectionClass($this->processTimedOutEventType());

            $processTimedOut = $reflectedClass->newInstance($this->processId(), $this->totalRetriesPermitted(), $this->retryCount());

        } catch (Exception $e) {
            throw new RuntimeException('Error creating new ProcessTimedOut instance because: ' . $e->getMessage());
        }

        return $processTimedOut;
    }

    private function setProcessId(ProcessId $aProcessId)
    {
        $this->assertArgumentNotNull($aProcessId, 'ProcessId is required.');

        $this->processId = $aProcessId;
    }

    private function setProcessTimedOutEventType($aProcessTimedOutEventType)
    {
        $this->assertArgumentNotEmpty($aProcessTimedOutEventType, 'ProcessTimedOutEventType is required.');

        $this->processTimedOutEventType = $aProcessTimedOutEventType;
    }

    private function setTenantId($aTenantId)
    {
        $this->assertArgumentNotEmpty($aTenantId, 'TenantId is required.');

        $this->tenantId = $aTenantId;
    }

    private function setTimeConstrainedProcessTrackerId($aTimeConstrainedProcessTrackerId)
    {
        $this->timeConstrainedProcessTrackerId = $aTimeConstrainedProcessTrackerId;
    }

    private function setTimeoutOccursOn($aTimeoutOccursOn)
    {
        $this->assertArgumentTrue($aTimeoutOccursOn > 0, 'Timeout must be greater than zero.');

        $this->timeoutOccursOn = $aTimeoutOccursOn;
    }

    private function setTotalRetriesPermitted($aTotalRetriesPermitted)
    {
        $this->assertArgumentTrue(
            $aTotalRetriesPermitted >= 0,
            'Total retries must be greater than or equal to zero.'
        );

        $this->totalRetriesPermitted = $aTotalRetriesPermitted;
    }

    private function totalRetriesReached()
    {
        return $this->retryCount() >= $this->totalRetriesPermitted();
    }
}
