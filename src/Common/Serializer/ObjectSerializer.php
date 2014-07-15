<?php

namespace SaasOvation\Common\Serializer;

class ObjectSerializer extends AbstractSerializer
{
    /**
     * @var ObjectSerializer
     */
    private static $eventSerializer;

    public static function instance()
    {
        if (null === self::$eventSerializer) {
            self::$eventSerializer = new ObjectSerializer();
        }

        return self::$eventSerializer;
    }

    public function deserialize($aSerialization, $aType)
    {
        $domainEvent = $this->serializer()->deserialize($aSerialization, $aType, 'json');

        return $domainEvent;
    }

    public function serialize($anObject)
    {
        return $this->serializer()->serialize($anObject, 'json');
    }
}
