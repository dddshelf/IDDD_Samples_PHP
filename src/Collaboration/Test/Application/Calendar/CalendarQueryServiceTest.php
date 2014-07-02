<?php

namespace SaasOvation\Collaboration\Test\Application\Calendar;

use SaasOvation\Collaboration\Test\Application\ApplicationTest;
use SaasOvation\Collaboration\Domain\Model\DomainRegistry;
use SaasOvation\Collaboration\Domain\Model\Calendar\CalendarSharer;
use SaasOvation\Collaboration\Domain\Model\Collaborator\Participant;

class CalendarQueryServiceTest extends ApplicationTest
{
    public function testQueryAllCalendars()
    {
        $calendars = $this->calendarAggregates();

        foreach ($calendars as $calendar) {
            DomainRegistry::calendarRepository()->save($calendar);
        }

        $queriedCalendars = $this->calendarQueryService->allCalendarsDataOfTenant($calendars[0]->tenant()->id());

        $this->assertNotNull($queriedCalendars);
        $this->assertNotEmpty($queriedCalendars);
        $this->assertCount(count($calendars), $queriedCalendars);

        foreach ($queriedCalendars as $calendarData) {
            $this->assertNotNull($calendarData);
            $this->assertEquals($calendars[0]->tenant()->id(), $calendarData->getTenantId());
            $this->assertNotNull($calendarData->getSharers());
            $this->assertNotEmpty($calendarData->getSharers());
        }
    }

    public function testQueryCalendar()
    {
        $calendar = $this->calendarAggregate();

        $sharerZoe = new CalendarSharer(
            new Participant('zoe', 'Zoe Doe', 'zoe@saasovation.com')
        );

        $calendar->shareCalendarWith($sharerZoe);

        $sharerJoe = new CalendarSharer(
            new Participant('joe', 'Joe Smith', 'joe@saasovation.com')
        );

        $calendar->shareCalendarWith($sharerJoe);

        DomainRegistry::calendarRepository()->save($calendar);

        $calendarData = $this->calendarQueryService->calendarDataOfId(
            $calendar->tenant()->id(),
            $calendar->calendarId()->id()
        );

        $this->assertNotNull($calendarData);
        $this->assertEquals($calendar->calendarId()->id(), $calendarData->getCalendarId());
        $this->assertEquals($calendar->description(), $calendarData->getDescription());
        $this->assertEquals($calendar->name(), $calendarData->getName());
        $this->assertEquals($calendar->owner()->emailAddress(), $calendarData->getOwnerEmailAddress());
        $this->assertEquals($calendar->owner()->identity(), $calendarData->getOwnerIdentity());
        $this->assertEquals($calendar->owner()->name(), $calendarData->getOwnerName());
        $this->assertEquals($calendar->tenant()->id(), $calendarData->getTenantId());
        $this->assertNotNull($calendarData->getSharers());
        $this->assertNotEmpty($calendarData->getSharers());
        $this->assertEquals(2, count($calendarData->getSharers()));

        foreach ($calendarData->getSharers() as $sharer) {
            if ('zoe' === $sharer->getParticipantIdentity()) {
                $this->assertEquals($calendar->calendarId()->id(), $sharer->getCalendarId());
                $this->assertEquals($sharerZoe->participant()->emailAddress(), $sharer->getParticipantEmailAddress());
                $this->assertEquals($sharerZoe->participant()->identity(), $sharer->getParticipantIdentity());
                $this->assertEquals($sharerZoe->participant()->name(), $sharer->getParticipantName());
            } else {
                $this->assertEquals($calendar->calendarId()->id(), $sharer->getCalendarId());
                $this->assertEquals($sharerJoe->participant()->emailAddress(), $sharer->getParticipantEmailAddress());
                $this->assertEquals($sharerJoe->participant()->identity(), $sharer->getParticipantIdentity());
                $this->assertEquals($sharerJoe->participant()->name(), $sharer->getParticipantName());
            }
        }
    }
}
