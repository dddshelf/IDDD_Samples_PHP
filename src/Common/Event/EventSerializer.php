<?php

namespace SaasOvation\Common\Event;

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
            static::$eventSerializer = new EventSerializer(false);
        }

        return static::$eventSerializer;
    }

    public function serialize(DomainEvent $aDomainEvent)
    {
        $serialization = $this->serializer()->serialize($aDomainEvent, 'json');

        return $serialization;
    }

    public function deserialize($aSerialization, $aType)
    {
        $domainEvent = $this->serializer()->deserialize($aSerialization, $aType, 'json');

        return $domainEvent;
    }
}
