<?php

namespace SaasOvation\Common\Domain\Model\Process;

use DateTimeImmutable;
use SaasOvation\Common\Domain\Model\DomainEvent;
use SaasOvation\Common\Domain\Model\ImplementsDomainEvent;

class ProcessTimedOut implements DomainEvent
{
    use ImplementsDomainEvent;

    /**
     * @var ProcessId
     */
    private $processId;

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
    private $totalRetriesPermitted;

    public function __construct($aTenantId, ProcessId $aProcessId, $aTotalRetriesPermitted, $aRetryCount)
    {
        $this->occurredOn = new DateTimeImmutable();
        $this->processId = $aProcessId;
        $this->retryCount = $aRetryCount;
        $this->tenantId = $aTenantId;
        $this->totalRetriesPermitted = $aTotalRetriesPermitted;
    }

    public function allowsRetries()
    {
        return $this->totalRetriesPermitted() > 0;
    }

    public function hasFullyTimedOut()
    {
        return !$this->allowsRetries() || $this->totalRetriesReached();
    }

    public function processId()
    {
        return $this->processId;
    }

    public function retryCount()
    {
        return $this->retryCount;
    }

    public function tenantId()
    {
        return $this->tenantId;
    }

    public function totalRetriesPermitted()
    {
        return $this->totalRetriesPermitted;
    }

    public function totalRetriesReached()
    {
        return $this->retryCount() >= $this->totalRetriesPermitted();
    }
}
