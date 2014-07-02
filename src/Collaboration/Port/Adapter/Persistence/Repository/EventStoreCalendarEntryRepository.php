<?php

namespace SaasOvation\Collaboration\Port\Adapter\Persistence\Repository;

use Rhumsaa\Uuid\Uuid;
use SaasOvation\Collaboration\Domain\Model\Calendar\CalendarEntry;
use SaasOvation\Collaboration\Domain\Model\Calendar\CalendarEntryId;
use SaasOvation\Collaboration\Domain\Model\Calendar\CalendarEntryRepository;
use SaasOvation\Collaboration\Domain\Model\Tenant\Tenant;
use SaasOvation\Collaboration\Port\Adapter\Persistence\EventStoreProvider;
use SaasOvation\Common\Event\Sourcing\EventStream;
use SaasOvation\Common\Event\Sourcing\EventStreamId;

class EventStoreCalendarEntryRepository extends EventStoreProvider implements CalendarEntryRepository
{
    public function calendarEntryOfId(Tenant $aTenant, CalendarEntryId $aCalendarEntryId)
    {
        // snapshots not currently supported; always use version 1
        $eventStream = $this->eventStore()->eventStreamSince(
            new EventStreamId($aTenant->id() . ':' . $aCalendarEntryId->id())
        );

        return new CalendarEntry($eventStream->events(), $eventStream->version());
    }

    public function nextIdentity()
    {
        return new CalendarEntryId(strtoupper(Uuid::uuid4()));
    }

    public function save(CalendarEntry $aCalendarEntry)
    {
        $aStreamName = $aCalendarEntry->tenant()->id() . ':' . $aCalendarEntry->calendarEntryId()->id();

        $eventId = new EventStreamId(
            $aStreamName,
            $aCalendarEntry->mutatedVersion()
        );

        $this->eventStore()->appendWith($eventId, $aCalendarEntry->mutatingEvents());

        $aCalendarEntry->mutatingEvents()->clear();
    }
}
