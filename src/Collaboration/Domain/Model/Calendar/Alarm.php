<?php

namespace SaasOvation\Collaboration\Domain\Model\Calendar;

use SaasOvation\Common\AssertionConcern;

final class Alarm extends AssertionConcern
{
    /**
     * @var int
     */
    private $alarmUnits;

    /**
     * @var AlarmUnitsType
     */
    private $alarmUnitsType;

    public function __construct(AlarmUnitsType $anAlarmUnitsType, $anAlarmUnits)
    {
        $this->setAlarmUnits($anAlarmUnits);
        $this->setAlarmUnitsType($anAlarmUnitsType);
    }

    /**
     * @return int
     */
    public function alarmUnits()
    {
        return $this->alarmUnits;
    }

    /**
     * @return AlarmUnitsType
     */
    public function alarmUnitsType()
    {
        return $this->alarmUnitsType;
    }

    public function equals($anObject)
    {
        $equalObjects = false;

        if (null !== $anObject && get_class($this) === get_class($anObject)) {
            $equalObjects =
                $this->alarmUnitsType()->equals($anObject->alarmUnitsType()) &&
                $this->alarmUnits() == $anObject->alarmUnits();
        }

        return $equalObjects;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return "Alarm [alarmUnits=" . $this->alarmUnits . ", alarmUnitsType=" . $this->alarmUnitsType . "]";
    }

    protected function setAlarmUnits($anAlarmUnits)
    {
        $this->alarmUnits = $anAlarmUnits;
    }

    protected function setAlarmUnitsType(AlarmUnitsType $anAlarmUnitsType)
    {
        $this->alarmUnitsType = $anAlarmUnitsType;
    }
}
