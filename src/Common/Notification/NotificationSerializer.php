<?php

namespace SaasOvation\Common\Notification;

use SaasOvation\Common\Serializer\AbstractSerializer;

class NotificationSerializer extends AbstractSerializer
{
    /**
     * @var NotificationSerializer
     */
    private static $notificationSerializer;

    public static function instance()
    {
        if (null === static::$notificationSerializer) {
            static::$notificationSerializer = new NotificationSerializer(false, false);
        }

        return static::$notificationSerializer;
    }

    public function serialize(Notification $aNotification)
    {
        return $this->serializer()->serialize($aNotification, 'json');
    }

    public function deserialize($aSerialization, $aType)
    {
        return $this->serializer()->deserialize($aSerialization, $aType, 'format');
    }
}
