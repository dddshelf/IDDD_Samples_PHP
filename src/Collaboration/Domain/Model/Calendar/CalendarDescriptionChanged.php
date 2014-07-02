<?php

namespace SaasOvation\Collaboration\Domain\Model\Calendar;

use DateTimeImmutable;
use DateTimeInterface;
use SaasOvation\Collaboration\Domain\Model\Tenant\Tenant;
use SaasOvation\Common\Domain\Model\DomainEvent;

class CalendarDescriptionChanged implements DomainEvent
{
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
        $aDescription
    ) {
        $this->calendarId = $aCalendarId;
        $this->description = $aDescription;
        $this->eventVersion = 1;
        $this->name = $aName;
        $this->occurredOn = new DateTimeImmutable();
        $this->tenant = $aTenant;
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
