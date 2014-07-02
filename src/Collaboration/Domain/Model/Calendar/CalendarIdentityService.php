<?php

namespace SaasOvation\Collaboration\Domain\Model\Calendar;

class CalendarIdentityService
{
    /**
     * @var CalendarRepository
     */
    private $calendarRepository;

    /**
     * @var CalendarEntryRepository
     */
    private $calendarEntryRepository;

    public function __construct(CalendarRepository $aCalendarRepository, CalendarEntryRepository $aCalendarEntryRepository)
    {
        $this->calendarRepository = $aCalendarRepository;
        $this->calendarEntryRepository = $aCalendarEntryRepository;
    }

    public function nextCalendarId()
    {
        return $this->calendarRepository()->nextIdentity();
    }

    public function nextCalendarEntryId()
    {
        return $this->calendarEntryRepository()->nextIdentity();
    }

    private function calendarRepository()
    {
        return $this->calendarRepository;
    }

    private function calendarEntryRepository()
    {
        return $this->calendarEntryRepository;
    }
}
