<?php

namespace SaasOvation\Collaboration\Domain\Model\Calendar;

use DateTimeImmutable;
use DateTimeInterface;
use SaasOvation\Collaboration\Domain\Model\Tenant\Tenant;
use SaasOvation\Common\Domain\Model\DomainEvent;

class CalendarUnshared implements DomainEvent
{
    /**
     * @var CalendarId
     */
    private $calendarId;

    /**
     * @var CalendarSharer
     */
    private $calendarSharer;

    /**
     * @var int
     */
    private $eventVersion;

    /**
     * @var string
     */
    private $name;

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
        $aName,
        CalendarSharer $aCalendarSharer
    ) {
        $this->calendarId = $aCalendarId;
        $this->calendarSharer = $aCalendarSharer;
        $this->eventVersion = 1;
        $this->name = $aName;
        $this->occurredOn = new DateTimeImmutable();
        $this->tenant = $aTenant;
    }

    public function calendarId()
    {
        return $this->calendarId;
    }

    public function calendarSharer()
    {
        return $this->calendarSharer;
    }

    public function eventVersion()
    {
        return $this->eventVersion;
    }

    public function name()
    {
        return $this->name;
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
