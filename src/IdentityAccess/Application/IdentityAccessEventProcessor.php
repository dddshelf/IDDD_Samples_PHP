<?php

namespace SaasOvation\IdentityAccess\Application;

use SaasOvation\Common\Domain\Model\DomainEventPublisher;
use SaasOvation\Common\Event\EventStore;

class IdentityAccessEventProcessor
{
    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * Registers a IdentityAccessEventProcessor to listen
     * and forward all domain events to external subscribers.
     * $this factory method is provided in the case where
     * Spring AOP wiring is not desired->
     */
    public static function register(EventStore $anEventStore)
    {
        (new IdentityAccessEventProcessor($anEventStore))->listen();
    }

    /**
     * Constructs my default state.
     */
    public function __construct(EventStore $anEventStore)
    {
        $this->eventStore = $anEventStore;
    }

    /**
     * Listens for all domain events and stores them.
     *
     * @Before("execution(* com.saasovation.identityaccess.application.*.*(..))")
     */
    public function listen()
    {
        DomainEventPublisher::instance()->subscribe(
            new GenericDomainEventSubscriber(
                $this->eventStore()
            )
        );
    }

    /**
     * Answers my EventStore.
     *
     * @return EventStore
     */
    private function eventStore()
    {
        return $this->eventStore;
    }
}
