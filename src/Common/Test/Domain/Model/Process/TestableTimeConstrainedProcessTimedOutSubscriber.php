<?php

namespace SaasOvation\Common\Test\Domain\Model\Process;

use SaasOvation\Common\Domain\Model\DomainEvent;
use SaasOvation\Common\Domain\Model\DomainEventSubscriber;

class TestableTimeConstrainedProcessTimedOutSubscriber implements DomainEventSubscriber
{
    /**
     * @var TimeConstrainedProcessTest
     */
    private $timeConstrainedProcessTest;

    /**
     * @param TimeConstrainedProcessTest $timeConstrainedProcessTest
     */
    public function __construct($timeConstrainedProcessTest)
    {
        $this->timeConstrainedProcessTest = $timeConstrainedProcessTest;
    }

    public function handleEvent(DomainEvent $aDomainEvent)
    {
        $this->timeConstrainedProcessTest->setReceived(true);
        $this->timeConstrainedProcessTest->process()->informTimeout($aDomainEvent->occurredOn());
    }

    public function subscribedToEventType()
    {
        return TestableTimeConstrainedProcessTimedOut::class;
    }
}
