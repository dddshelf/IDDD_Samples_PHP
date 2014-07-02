<?php

namespace SaasOvation\Collaboration\Domain\Model\Calendar;

use DateTimeImmutable;
use DateTimeInterface;
use SaasOvation\Collaboration\Domain\Model\Collaborator\Participant;
use SaasOvation\Collaboration\Domain\Model\Tenant\Tenant;
use SaasOvation\Common\Domain\Model\DomainEvent;

class CalendarEntryParticipantInvited implements DomainEvent
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
     * @var DateTimeInterface
     */
    private $occurredOn;

    /**
     * @var Participant
     */
    private $participant;

    /**
     * @var Tenant
     */
    private $tenant;

    public function __construct(
        Tenant $aTenant,
        CalendarId $aCalendarId,
        CalendarEntryId $aCalendarEntryId,
        Participant $aParticipant
    ) {
        $this->calendarEntryId = $aCalendarEntryId;
        $this->calendarId = $aCalendarId;
        $this->eventVersion = 1;
        $this->occurredOn = new DateTimeImmutable();
        $this->participant = $aParticipant;
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

    public function occurredOn()
    {
        return $this->occurredOn;
    }

    public function participant()
    {
        return $this->participant;
    }

    public function tenant()
    {
        return $this->tenant;
    }
}
