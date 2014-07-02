<?php

namespace SaasOvation\Collaboration\Domain\Model\Calendar;

use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use SaasOvation\Collaboration\Domain\Model\Collaborator\Owner;
use SaasOvation\Collaboration\Domain\Model\Tenant\Tenant;
use SaasOvation\Common\Domain\Model\DomainEvent;

class CalendarCreated implements DomainEvent
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
     * @var DateTimeImmutable
     */
    private $occurredOn;

    /**
     * @var Owner
     */
    private $owner;

    /**
     * @var Collection
     */
    private $sharedWith;

    /**
     * @var Tenant
     */
    private $tenant;

    public function __construct(
        Tenant $aTenant,
        CalendarId $aCalendarId,
        $aName,
        $aDescription,
        Owner $anOwner,
        Collection $aSharedWith
    ) {
        $this->calendarId = $aCalendarId;
        $this->description = $aDescription;
        $this->eventVersion = 1;
        $this->name = $aName;
        $this->occurredOn = new DateTimeImmutable();
        $this->owner = $anOwner;
        $this->sharedWith = $aSharedWith;
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

    public function owner()
    {
        return $this->owner;
    }

    public function sharedWith()
    {
        return $this->sharedWith;
    }

    public function tenant()
    {
        return $this->tenant;
    }
}
