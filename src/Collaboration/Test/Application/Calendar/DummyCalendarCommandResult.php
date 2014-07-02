<?php

namespace SaasOvation\Collaboration\Test\Application\Calendar;

use BadMethodCallException;
use SaasOvation\Collaboration\Application\Calendar\Data\CalendarCommandResult;

class DummyCalendarCommandResult implements CalendarCommandResult
{
    private $calendarId;
    private $calendarEntryId;

    private $shouldThrowExceptionOnResultingCalendarEntryIdMethodCall = true;

    public function resultingCalendarId($aCalendarId)
    {
        $this->calendarId = $aCalendarId;
    }

    public function getCalendarId()
    {
        return $this->calendarId;
    }

    public function setShouldThrowExceptionOnResultingCalendarEntryIdMethodCall($throw)
    {
        $this->shouldThrowExceptionOnResultingCalendarEntryIdMethodCall = $throw;
    }

    public function resultingCalendarEntryId($aCalendarEntryId)
    {
        if ($this->shouldThrowExceptionOnResultingCalendarEntryIdMethodCall) {
            throw new BadMethodCallException('Should not be reached.');
        }

        $this->calendarEntryId = $aCalendarEntryId;
    }

    public function setCalendarEntryId($calendarEntryId)
    {
        $this->calendarEntryId = $calendarEntryId;
    }

    public function getCalendarEntryId()
    {
        return $this->calendarEntryId;
    }
}
