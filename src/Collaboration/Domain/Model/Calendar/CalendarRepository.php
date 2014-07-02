<?php

namespace SaasOvation\Collaboration\Domain\Model\Calendar;

use SaasOvation\Collaboration\Domain\Model\Tenant\Tenant;

interface CalendarRepository
{
    /**
     * @param Tenant $aTenant
     * @param CalendarId $aCalendarId
     *
     * @return Calendar
     */
    public function calendarOfId(Tenant $aTenant, CalendarId $aCalendarId);

    /**
     * @return CalendarId
     */
    public function nextIdentity();

    public function save(Calendar $aCalendar);
}
