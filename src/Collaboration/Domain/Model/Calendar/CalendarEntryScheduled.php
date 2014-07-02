<?php

namespace SaasOvation\Collaboration\Domain\Model\Calendar;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use SaasOvation\Collaboration\Domain\Model\Collaborator\Owner;
use SaasOvation\Collaboration\Domain\Model\Tenant\Tenant;
use SaasOvation\Common\Domain\Model\DomainEvent;

class CalendarEntryScheduled implements DomainEvent
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
     * @var string
     */
    private $description;

    /**
     * @var int
     */
    private $eventVersion;

    /**
     * @var array
     */
    private $invitees;

    /**
     * @var string
     */
    private $location;

    /**
     * @var DateTimeInterface
     */
    private $occurredOn;

    /**
     * @var Owner
     */
    private $owner;

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
        $aDescription,
        $aLocation,
        Owner $anOwner,
        TimeSpan $aTimeSpan,
        Repetition $aRepetition,
        Alarm $anAlarm,
        Collection $anInvitees
    ) {
        $this->alarm = $anAlarm;
        $this->calendarEntryId = $aCalendarEntryId;
        $this->calendarId = $aCalendarId;
        $this->description = $aDescription;
        $this->eventVersion = 1;
        $this->invitees = $anInvitees->toArray();
        $this->location = $aLocation;
        $this->occurredOn = new DateTimeImmutable();
        $this->owner = $anOwner;
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

    public function description()
    {
        return $this->description;
    }

    public function eventVersion()
    {
        return $this->eventVersion;
    }

    public function invitees()
    {
        return new ArrayCollection($this->invitees);
    }

    public function location()
    {
        return $this->location;
    }

    public function occurredOn()
    {
        return $this->occurredOn;
    }

    public function owner()
    {
        return $this->owner;
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
