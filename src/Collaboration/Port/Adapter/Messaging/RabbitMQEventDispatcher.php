<?php

namespace SaasOvation\Collaboration\Port\Adapter\Messaging;

use SaasOvation\common\Event\Sourcing\DispatchableDomainEvent;
use SaasOvation\common\Event\Sourcing\EventDispatcher;
use SaasOvation\common\Notification\Notification;
use SaasOvation\common\Notification\NotificationSerializer;
use SaasOvation\common\Port\Adapter\Messaging\Exchanges;
use SaasOvation\common\Port\Adapter\Messaging\RabbitMQ\ConnectionSettings;
use SaasOvation\common\Port\Adapter\Messaging\RabbitMQ\Exchange;
use SaasOvation\common\Port\Adapter\Messaging\RabbitMQ\MessageParameters;
use SaasOvation\common\Port\Adapter\Messaging\RabbitMQ\MessageProducer;
use Symfony\Component\DependencyInjection\Exception\BadMethodCallException;

class RabbitMQEventDispatcher implements EventDispatcher
{
    /**
     * @var MessageProducer
     */
    private $messageProducer;

    public function __construct(EventDispatcher $aParentEventDispatcher)
    {
        $this->initializeMessageProducer();
        $aParentEventDispatcher->registerEventDispatcher($this);
    }

    public function dispatch(DispatchableDomainEvent $aDispatchableDomainEvent)
    {
        $notification = new Notification(
            $aDispatchableDomainEvent->eventId(),
            $aDispatchableDomainEvent->domainEvent()
        );

        $messageParameters = MessageParameters::durableTextParameters(
            $notification->typeName(),
            (string) $notification->notificationId(),
            $notification->occurredOn()
        );

        $serializedNotification = NotificationSerializer::instance()->serialize($notification);

        $this->messageProducer->send($serializedNotification, $messageParameters);
    }

    public function registerEventDispatcher(EventDispatcher $anEventDispatcher)
    {
        throw new BadMethodCallException('Cannot register additional dispatchers.');
    }

    public function understands(DispatchableDomainEvent $aDispatchableDomainEvent)
    {
        return true;
    }

    private function initializeMessageProducer()
    {
        $exchange = Exchange::fanOutInstance(
            ConnectionSettings::instance(),
            Exchanges::$COLLABORATION_EXCHANGE_NAME,
            true
        );

        $this->messageProducer = MessageProducer::instance($exchange);
    }
}
