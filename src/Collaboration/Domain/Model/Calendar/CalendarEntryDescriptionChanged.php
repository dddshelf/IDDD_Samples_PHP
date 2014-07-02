<?php

namespace SaasOvation\Collaboration\Domain\Model\Calendar;

use DateTimeImmutable;
use DateTimeInterface;
use SaasOvation\Collaboration\Domain\Model\Tenant\Tenant;
use SaasOvation\Common\Domain\Model\DomainEvent;

class CalendarEntryDescriptionChanged implements DomainEvent
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
     * @var string
     */
    private $description;

    /**
     * @var int
     */
    private $eventVersion;

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
        $aDescription
    ) {
        $this->calendarEntryId  = $aCalendarEntryId;
        $this->calendarId       = $aCalendarId;
        $this->description      = $aDescription;
        $this->eventVersion     = 1;
        $this->occurredOn       = new DateTimeImmutable();
        $this->tenant           = $aTenant;
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

    public function occurredOn()
    {
        return $this->occurredOn;
    }

    public function tenant()
    {
        return $this->tenant;
    }
}
