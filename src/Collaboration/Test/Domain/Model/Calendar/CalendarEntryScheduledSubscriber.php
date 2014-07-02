<?php

namespace SaasOvation\Collaboration\Test\Domain\Model\Calendar;

use SaasOvation\Collaboration\Domain\Model\Calendar\CalendarEntryId;
use SaasOvation\Collaboration\Domain\Model\Calendar\CalendarEntryScheduled;
use SaasOvation\Common\Domain\Model\DomainEvent;
use SaasOvation\Common\Domain\Model\DomainEventSubscriber;

class CalendarEntryScheduledSubscriber implements DomainEventSubscriber
{
    /**
     * @var CalendarEntryId
     */
    private $calendarEntryId;

    public function handleEvent(DomainEvent $aDomainEvent)
    {
        $this->calendarEntryId = $aDomainEvent->calendarEntryId();
    }

    public function subscribedToEventType()
    {
        return CalendarEntryScheduled::class;
    }

    public function getCalendarEntryId()
    {
        return $this->calendarEntryId;
    }
}