<?php

namespace SaasOvation\Collaboration\Domain\Model\Calendar;

use DateTimeImmutable;
use DateTimeInterface;
use SaasOvation\Collaboration\Domain\Model\Tenant\Tenant;
use SaasOvation\Common\Domain\Model\DomainEvent;

class CalendarEntryRescheduled implements DomainEvent
{
    /**
     * @var Alarm
     */
    private $alarm;

    /**
     * @var CalendarEntryId
     */
    private $calendarEntryId;

    /**
     * @var CalendarId
     */
    private $calendarId;

    /**
     * @var int
     */
    private $eventVersion;

    /**
     * @var DateTimeInterface
     */
    private $occurredOn;

    /**
     * @var Repetition
     */
    private $repetition;

    /**
     * @var Tenant
     */
    private $tenant;

    /**
     * @var TimeSpan
     */
    private $timeSpan;

    public function __construct(
        Tenant $aTenant,
        CalendarId $aCalendarId,
        CalendarEntryId $aCalendarEntryId,
        TimeSpan $aTimeSpan,
        Repetition $aRepetition,
        Alarm $anAlarm
    ) {
        $this->alarm = $anAlarm;
        $this->calendarEntryId = $aCalendarEntryId;
        $this->calendarId = $aCalendarId;
        $this->eventVersion = 1;
        $this->occurredOn = new DateTimeImmutable();
        $this->repetition = $aRepetition;
        $this->tenant = $aTenant;
        $this->timeSpan = $aTimeSpan;
    }

    public function alarm()
    {
        return $this->alarm;
    }

    public function calendarEntryId()
    {
        return $this->calendarEntryId;
    }

    public function calendarId()
    {
        return $this->calendarId;
    }

    public function eventVersion()
    {
        return $this->eventVersion;
    }

    public function occurredOn()
    {
        return $this->occurredOn;
    }

    public function repetition()
    {
        return $this->repetition;
    }

    public function tenant()
    {
        return $this->tenant;
    }

    public function timeSpan()
    {
        return $this->timeSpan;
    }
}
