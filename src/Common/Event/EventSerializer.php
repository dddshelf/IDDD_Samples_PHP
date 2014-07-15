<?php

namespace SaasOvation\Common\Event;

use JMS\Serializer\SerializationContext;
use SaasOvation\Common\Domain\Model\DomainEvent;
use SaasOvation\Common\Serializer\AbstractSerializer;

class EventSerializer extends AbstractSerializer
{
    /**
     * @var EventSerializer
     */
    private static $eventSerializer;

    public static function instance()
    {
        if (null === static::$eventSerializer) {
            static::$eventSerializer = new EventSerializer();
        }

        return static::$eventSerializer;
    }

    public function serialize(DomainEvent $aDomainEvent)
    {
        $context = new SerializationContext();
        $context->setSerializeNull(true);

        $serialization = $this->serializer()->serialize($aDomainEvent, 'json', $context);

        return $serialization;
    }

    public function deserialize($aSerialization, $aType)
    {
        $domainEvent = $this->serializer()->deserialize($aSerialization, $aType, 'json');

        return $domainEvent;
    }
}
