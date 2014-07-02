<?php

namespace SaasOvation\Common\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class DomainEventPublisher
{
    /**
     * @var DomainEventPublisher
     */
    private static $instance;

    /**
     * @var Collection
     */
    private $subscribers;

    public static function instance()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    public function publish(DomainEvent $aDomainEvent)
    {
        if ($this->hasSubscribers()) {
            $eventType = get_class($aDomainEvent);

            $allSubscribers = $this->subscribers();

            foreach ($allSubscribers as $subscriber) {
                $subscribedToType = $subscriber->subscribedToEventType();

                if ($eventType === $subscribedToType || $subscribedToType == 'SaasOvation\Common\Domain\Model\DomainEvent') {
                    $subscriber->handleEvent($aDomainEvent);
                }
            }
        }
    }

    public function publishAll(Collection $aDomainEvents)
    {
        foreach ($aDomainEvents as $domainEvent) {
            $this->publish($domainEvent);
        }
    }

    public function reset()
    {
        $this->subscribers = null;
    }

    public function subscribe(DomainEventSubscriber $aSubscriber)
    {
        $this->ensureSubscribersList();

        $this->subscribers()->add($aSubscriber);
    }

    private function __construct()
    {
        $this->ensureSubscribersList();
    }

    private function ensureSubscribersList()
    {
        if (!$this->hasSubscribers()) {
            $this->setSubscribers(new ArrayCollection());
        }
    }

    private function hasSubscribers()
    {
        return null !== $this->subscribers();
    }

    private function subscribers()
    {
        return $this->subscribers;
    }

    private function setSubscribers(Collection $aSubscriberList)
    {
        $this->subscribers = $aSubscriberList;
    }
}
