<?php

namespace SaasOvation\Collaboration\Port\Adapter\Persistence\Repository;

use Rhumsaa\Uuid\Uuid;
use SaasOvation\Collaboration\Domain\Model\Calendar\Calendar;
use SaasOvation\Collaboration\Domain\Model\Calendar\CalendarId;
use SaasOvation\Collaboration\Domain\Model\Calendar\CalendarRepository;
use SaasOvation\Collaboration\Domain\Model\Tenant\Tenant;
use SaasOvation\Collaboration\Port\Adapter\Persistence\EventStoreProvider;
use SaasOvation\Common\Event\Sourcing\EventStreamId;

class EventStoreCalendarRepository extends EventStoreProvider implements CalendarRepository
{
    public function calendarOfId(Tenant $aTenant, CalendarId $aCalendarId)
    {
        $eventId = new EventStreamId($aTenant->id() . ':' . $aCalendarId->id());

        $eventStream = $this->eventStore()->eventStreamSince($eventId);

        $calendar = new Calendar($eventStream->events(), $eventStream->version());

        return $calendar;
    }

    public function nextIdentity()
    {
        return new CalendarId(strtoupper(Uuid::uuid4()));
    }

    public function save(Calendar $aCalendar)
    {
        $streamName = $aCalendar->tenant()->id() . ':' . $aCalendar->calendarId()->id();

        $eventId = new EventStreamId(
            $streamName,
            $aCalendar->mutatedVersion()
        );

        $this->eventStore()->appendWith($eventId, $aCalendar->mutatingEvents());

        $aCalendar->mutatingEvents()->clear();
    }
}
