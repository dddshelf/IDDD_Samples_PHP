<?php

namespace SaasOvation\Collaboration\Application\Calendar\Data;

interface CalendarCommandResult
{
    public function resultingCalendarId($aCalendarId);

    public function resultingCalendarEntryId($aCalendarEntryId);
}
