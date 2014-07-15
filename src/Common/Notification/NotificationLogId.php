<?php

namespace SaasOvation\Common\Notification;

class NotificationLogId
{
    /**
     * @var int
     */
    private $low;

    /**
     * @var int
     */
    private $high;

    public static function encoded(NotificationLogId $aNotificationLogId = null)
    {
        if (null !== $aNotificationLogId) {
            return $aNotificationLogId->getEncoded();
        }
    }

    public static function first($aNotificationsPerLog)
    {
        $id = NotificationLogId::createFromBounds(0, 0);

        return $id->next($aNotificationsPerLog);
    }

    public static function createFromBounds($aLowId, $aHighId)
    {
        $instance = new static();

        $instance->setLow($aLowId);
        $instance->setHigh($aHighId);

        return $instance;
    }

    public static function createFromNotificationLogId($aNotificationLogId)
    {
        $textIds = explode(',', $aNotificationLogId);

        return static::createFromBounds(
            intval($textIds[0]),
            intval($textIds[1])
        );
    }

    public function getEncoded()
    {
        return $this->low() . ',' . $this->high();
    }

    public function low()
    {
        return $this->low;
    }

    public function high()
    {
        return $this->high;
    }

    public function next($aNotificationsPerLog)
    {
        $nextLow = $this->high() + 1;

        // ensures a minted id value even though there may
        // not be $this many notifications at present
        $nextHigh = $nextLow + $aNotificationsPerLog - 1;

        $next = NotificationLogId::createFromBounds($nextLow, $nextHigh);

        if ($this->equals($next)) {
            $next = null;
        }

        return $next;
    }

    public function previous($aNotificationsPerLog)
    {
        $previousLow = max($this->low() - $aNotificationsPerLog, 1);

        $previousHigh = $previousLow + $aNotificationsPerLog - 1;

        $previous = NotificationLogId::createFromBounds($previousLow, $previousHigh);

        if ($this->equals($previous)) {
            $previous = null;
        }

        return $previous;
    }

    public function equals($anObject)
    {
        $equalObjects = false;

        if (null !== $anObject && get_class($this) === get_class($anObject)) {
            $equalObjects = $this->low() === $anObject->low()
                && $this->high() == $anObject->high();
        }

        return $equalObjects;
    }

    public function __toString()
    {
        return 'NotificationLogId [low=' . $this->low . ', high=' . $this->high . ']';
    }

    private function setLow($aLow)
    {
        $this->low = $aLow;
    }

    private function setHigh($aHigh)
    {
        $this->high = $aHigh;
    }
}
