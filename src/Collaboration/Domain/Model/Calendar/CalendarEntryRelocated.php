<?php

namespace SaasOvation\Collaboration\Domain\Model\Calendar;

use DateTimeImmutable;
use DateTimeInterface;
use SaasOvation\Collaboration\Domain\Model\Tenant\Tenant;
use SaasOvation\Common\Domain\Model\DomainEvent;

class CalendarEntryRelocated implements DomainEvent
{
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
     * @var string
     */
    private $location;

    /**
     * @var DateTimeInterface
     */
    private $occurredOn;

    /**
     * @var Tenant
     */
    private $tenant;

    public function __construct(
        Tenant $aTenant,
        CalendarId $aCalendarId,
        CalendarEntryId $aCalendarEntryId,
        $aLocation
    ) {
        $this->calendarEntryId = $aCalendarEntryId;
        $this->calendarId = $aCalendarId;
        $this->eventVersion = 1;
        $this->location = $aLocation;
        $this->occurredOn = new DateTimeImmutable();
        $this->tenant = $aTenant;
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

    public function location()
    {
        return $this->location;
    }

    public function occurredOn()
    {
        return $this->occurredOn;
    }

    public function tenant()
    {
        return $this->tenant;
    }
}
