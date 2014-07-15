<?php

namespace SaasOvation\Common\Test\Port\Adapter\Persistence\LevelDB;

use SaasOvation\Common\Domain\Model\DomainEvent;
use SaasOvation\Common\Domain\Model\DomainEventSubscriber;
use SaasOvation\Common\Test\Domain\Model\Process\TestableTimeConstrainedProcessTimedOut;

class TestableTimeConstrainedProcessTimedOutSubscriber implements DomainEventSubscriber
{
    /**
     * @var LevelDBTimeConstrainedProcessTrackerRepositoryTest
     */
    private $levelDBTimeConstrainedProcessTrackerRepositoryTest;

    public function __construct(LevelDBTimeConstrainedProcessTrackerRepositoryTest $levelDBTimeConstrainedProcessTrackerRepositoryTest)
    {
        $this->levelDBTimeConstrainedProcessTrackerRepositoryTest = $levelDBTimeConstrainedProcessTrackerRepositoryTest;
    }

    public function handleEvent(DomainEvent $aDomainEvent)
    {
        $this->levelDBTimeConstrainedProcessTrackerRepositoryTest->setReceived(true);
        $this->levelDBTimeConstrainedProcessTrackerRepositoryTest->process()->informTimeout($aDomainEvent->occurredOn());
    }

    public function subscribedToEventType() {
        return TestableTimeConstrainedProcessTimedOut::class;
    }
}