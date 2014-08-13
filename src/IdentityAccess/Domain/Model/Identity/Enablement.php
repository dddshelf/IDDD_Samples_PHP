<?php

namespace SaasOvation\IdentityAccess\Domain\Model\Identity;

use DateTime;
use DateTimeInterface;
use SaasOvation\Common\AssertionConcern;

final class Enablement extends AssertionConcern
{
    /**
     * @var boolean
     */
    private $enabled;

    /**
     * @var DateTimeInterface
     */
    private $endDate;

    /**
     * @var DateTimeInterface
     */
    private $startDate;

    public static function indefiniteEnablement()
    {
        return new Enablement(true, null, null);
    }

    public function __construct($anEnabled, DateTimeInterface $aStartDate = null, DateTimeInterface $anEndDate = null)
    {
        if (null !== $aStartDate || null !== $anEndDate) {
            $this->assertArgumentNotNull($aStartDate, 'The start date must be provided.');
            $this->assertArgumentNotNull($anEndDate, 'The end date must be provided.');
            $this->assertArgumentFalse($aStartDate > $anEndDate, 'Enablement start and/or end date is invalid.');
        }
    
        $this->setEnabled($anEnabled);
        $this->setEndDate($anEndDate);
        $this->setStartDate($aStartDate);
    }

    public static function createFromEnablement(Enablement $anEnablement)
    {
        return new Enablement(
            $anEnablement->isEnabled(),
            $anEnablement->startDate(),
            $anEnablement->endDate()
        );
    }

    public function isEnabled()
    {
        return $this->enabled;
    }

    public function isEnablementEnabled()
    {
        $enabled = false;

        if ($this->isEnabled()) {
            if (!$this->isTimeExpired()) {
                $enabled = true;
            }
        }

        return $enabled;
    }

    public function endDate()
    {
        return $this->endDate;
    }

    public function isTimeExpired()
    {
        $timeExpired = false;

        if (null !== $this->startDate() && null !== $this->endDate()) {
            $now = new DateTime();
            if ($now < $this->startDate()
                || $now > $this->endDate()
            ) {
                $timeExpired = true;
            }
        }

        return $timeExpired;
    }

    public function startDate()
    {
        return $this->startDate;
    }

    public function equals($anObject)
    {
        $equalObjects = false;

        if ($anObject !== null && get_class($this) === get_class($anObject)) {
            $equalObjects =
                $this->isEnabled() == $anObject->isEnabled() &&
                (($this->startDate() == null && $anObject->startDate() == null) ||
                    ($this->startDate() != null && $this->startDate()->equals($anObject->startDate()))) &&
                (($this->endDate() == null && $anObject->endDate() == null) ||
                    ($this->endDate() != null && $this->endDate()->equals($anObject->endDate())));
        }

        return $equalObjects;
    }

    public function __toString()
    {
        return 'Enablement [enabled=' . $this->enabled . ', endDate=' . $this->endDate . ', startDate=' . $this->startDate . ']';
    }

    private function setEnabled($anEnabled)
    {
        $this->enabled = $anEnabled;
    }

    private function setEndDate(DateTimeInterface $anEndDate = null)
    {
        $this->endDate = $anEndDate;
    }

    private function setStartDate(DateTimeInterface $aStartDate = null)
    {
        $this->startDate = $aStartDate;
    }
}
