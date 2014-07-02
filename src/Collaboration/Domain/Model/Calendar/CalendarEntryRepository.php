<?php

namespace SaasOvation\Collaboration\Domain\Model\Calendar;

use SaasOvation\Collaboration\Domain\Model\Tenant\Tenant;

interface CalendarEntryRepository
{
    /**
     * Finds a CalendarEntry given a Tenant and a CalendarEntryId
     *
     * @param Tenant $aTenant
     * @param CalendarEntryId $aCalendarEntryId
     *
     * @return CalendarEntry
     */
    public function calendarEntryOfId(Tenant $aTenant, CalendarEntryId $aCalendarEntryId);

    /**
     * @return CalendarEntryId
     */
    public function nextIdentity();

    public function save(CalendarEntry $aCalendarEntry);
}
