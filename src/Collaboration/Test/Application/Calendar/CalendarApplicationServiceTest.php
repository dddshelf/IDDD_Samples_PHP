<?php

namespace SaasOvation\Collaboration\Test\Application\Calendar;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use SaasOvation\Collaboration\Test\Application\ApplicationTest;
use SaasOvation\Collaboration\Domain\Model\DomainRegistry;
use SaasOvation\Collaboration\Domain\Model\Calendar\CalendarEntryId;
use SaasOvation\Collaboration\Domain\Model\Calendar\CalendarId;
use SaasOvation\Collaboration\Domain\Model\Tenant\Tenant;

class CalendarApplicationServiceTest extends ApplicationTest
{
    /**
     * @test
     */
    public function changeCalendarDescription()
    {
        $calendar = $this->calendarAggregate();

        DomainRegistry::calendarRepository()->save($calendar);

        $this->calendarApplicationService->changeCalendarDescription(
            $calendar->tenant()->id(),
            $calendar->calendarId()->id(),
            'This is a changed description.'
        );

        $changedCalendar = DomainRegistry::calendarRepository()->calendarOfId(
            $calendar->tenant(),
            $calendar->calendarId()
        );

        $this->assertNotNull($changedCalendar);
        $this->assertNotEquals($calendar->description(), $changedCalendar->description());
        $this->assertEquals('This is a changed description.', $changedCalendar->description());
    }

    /**
     * @test
     */
    public function createCalendar()
    {
        $result = new DummyCalendarCommandResult();

        $tenantId = '01234567';

        $sharerWith = new ArrayCollection();
        $sharerWith->add('participant1');

        $this->calendarApplicationService->createCalendar(
            $tenantId,
            'Personal Training',
            'My personal training calendar.',
            'owner1',
            $sharerWith,
            $result
        );

        $calendarRepository = DomainRegistry::calendarRepository();
        $aCalendarId = $result->getCalendarId();

        $calendar = $calendarRepository->calendarOfId(
            new Tenant($tenantId),
            new CalendarId($aCalendarId)
        );

        $this->assertNotNull($calendar);
        $this->assertEquals($tenantId, $calendar->tenant()->id());
        $this->assertEquals($aCalendarId, $calendar->calendarId()->id());
        $this->assertEquals('Personal Training', $calendar->name());
        $this->assertEquals('My personal training calendar.', $calendar->description());
        $this->assertEquals('owner1', $calendar->owner()->identity());
        $this->assertEquals(1, $calendar->allSharedWith()->count());
        $this->assertEquals(
            'participant1',
            $calendar->allSharedWith()->current()->participant()->identity()
        );
    }

    /**
     * @test
     */
    public function renameCalendar()
    {
        $calendar = $this->calendarAggregate();

        DomainRegistry::calendarRepository()->save($calendar);

        $this->calendarApplicationService->renameCalendar(
            $calendar->tenant()->id(),
            $calendar->calendarId()->id(),
            'My Training Calendar'
        );

        $changedCalendar = DomainRegistry::calendarRepository()->calendarOfId(
            $calendar->tenant(),
            $calendar->calendarId()
        );

        $this->assertNotNull($changedCalendar);
        $this->assertNotEquals($calendar->name(), $changedCalendar->name());
        $this->assertEquals('My Training Calendar', $changedCalendar->name());
    }

    /**
     * @test
     */
    public function scheduleCalendarEntry()
    {
        $calendar = $this->calendarAggregate();

        DomainRegistry::calendarRepository()->save($calendar);

        $result = new DummyCalendarCommandResult();
        $result->setShouldThrowExceptionOnResultingCalendarEntryIdMethodCall(false);

        $now = new DateTimeImmutable();
        $nextWeek = (new DateTimeImmutable())->setTimestamp($now->getTimestamp() + (86400000 * 7));
        $nextWeekPlusOneHour = (new DateTimeImmutable())->setTimestamp($nextWeek->getTimestamp() + (1000 * 60 * 60));

        $this->calendarApplicationService->scheduleCalendarEntry(
            $calendar->tenant()->id(),
            $calendar->calendarId()->id(),
            'My annual checkup appointment',
            'Family Health Offices',
            'owner1',
            $nextWeek,
            $nextWeekPlusOneHour,
            'DoesNotRepeat',
            $nextWeekPlusOneHour,
            'Hours',
            8,
            new ArrayCollection(),
            $result
        );

        $calendarEntry = DomainRegistry::calendarEntryRepository()->calendarEntryOfId(
            $calendar->tenant(),
            new CalendarEntryId($result->getCalendarEntryId())
        );

        $this->assertNotNull($calendarEntry);
        $this->assertEquals('My annual checkup appointment', $calendarEntry->description());
        $this->assertEquals('Family Health Offices', $calendarEntry->location());
        $this->assertEquals('owner1', $calendarEntry->owner()->identity());
    }

    /**
     * @test
     */
    public function shareCalendarWith()
    {
        $calendar = $this->calendarAggregate();

        DomainRegistry::calendarRepository()->save($calendar);

        $sharerWith = new ArrayCollection();
        $sharerWith->add('participant1');
        $sharerWith->add('participant2');

        $this->calendarApplicationService->shareCalendarWith(
            $calendar->tenant()->id(),
            $calendar->calendarId()->id(),
            $sharerWith
        );

        $sharedCalendar = DomainRegistry::calendarRepository()->calendarOfId(
            $calendar->tenant(),
            $calendar->calendarId()
        );

        $this->assertNotNull($sharedCalendar);
        $this->assertEquals(2, $sharedCalendar->allSharedWith()->count());

        foreach ($sharedCalendar->allSharedWith() as $sharer) {
            $this->assertContains(
                $sharer->participant()->identity(),
                [
                    'participant1',
                    'participant2'
                ]
            );
        }
    }

    /**
     * @test
     */
    public function unshareCalendarWith()
    {
        $calendar = $this->calendarAggregate();

        DomainRegistry::calendarRepository()->save($calendar);

        $sharerWith = new ArrayCollection();
        $sharerWith->add('participant1');
        $sharerWith->add('participant2');
        $sharerWith->add('participant3');

        $this->calendarApplicationService->shareCalendarWith(
            $calendar->tenant()->id(),
            $calendar->calendarId()->id(),
            $sharerWith
        );

        $sharedCalendar = DomainRegistry::calendarRepository()->calendarOfId(
            $calendar->tenant(),
            $calendar->calendarId()
        );

        $this->assertNotNull($sharedCalendar);
        $this->assertEquals(3, $sharedCalendar->allSharedWith()->count());

        $unsharerWith = new ArrayCollection($sharerWith->toArray());
        $this->assertTrue($unsharerWith->removeElement('participant2'));

        $this->calendarApplicationService->unshareCalendarWith(
            $calendar->tenant()->id(),
            $calendar->calendarId()->id(),
            $unsharerWith
        );

        $sharedCalendar = DomainRegistry::calendarRepository()->calendarOfId(
            $calendar->tenant(),
            $calendar->calendarId()
        );

        $this->assertEquals(1, $sharedCalendar->allSharedWith()->count());
        $this->assertEquals('participant2', $sharedCalendar->allSharedWith()->current()->participant()->identity());
    }
}

