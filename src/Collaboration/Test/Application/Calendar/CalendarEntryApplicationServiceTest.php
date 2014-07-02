<?php

namespace SaasOvation\Collaboration\Test\Application\Calendar;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use SaasOvation\Collaboration\Test\Application\ApplicationTest;
use SaasOvation\Collaboration\Domain\Model\DomainRegistry;

class CalendarEntryApplicationServiceTest extends ApplicationTest
{
    public function testChangeCalendarEntryDescription()
    {
        $calendarEntry = $this->calendarEntryAggregate();

        DomainRegistry::calendarEntryRepository()->save($calendarEntry);

        $this->calendarEntryApplicationService->changeCalendarEntryDescription(
            $calendarEntry->tenant()->id(),
            $calendarEntry->calendarEntryId()->id(),
            'A changed calendar entry description.'
        );

        $changedCalendarEntry = DomainRegistry::calendarEntryRepository()->calendarEntryOfId(
            $calendarEntry->tenant(),
            $calendarEntry->calendarEntryId()
        );

        $this->assertNotNull($changedCalendarEntry);
        $this->assertEquals('A changed calendar entry description.', $changedCalendarEntry->description());
    }

    public function testInviteCalendarEntryParticipant()
    {
        $calendarEntry = $this->calendarEntryAggregate();

        DomainRegistry::calendarEntryRepository()->save($calendarEntry);

        $invitees = new ArrayCollection();
        $invitees->add('participant1');
        $invitees->add('participant2');
        $invitees->add('participant3');

        $this->calendarEntryApplicationService->inviteCalendarEntryParticipant(
            $calendarEntry->tenant()->id(),
            $calendarEntry->calendarEntryId()->id(),
            $invitees
        );

        $changedCalendarEntry = DomainRegistry::calendarEntryRepository()->calendarEntryOfId(
            $calendarEntry->tenant(),
            $calendarEntry->calendarEntryId()
        );

        $this->assertNotNull($changedCalendarEntry);
        $this->assertEquals(3, $changedCalendarEntry->allInvitees()->count());

        foreach ($changedCalendarEntry->allInvitees() as $invitee) {
            $this->assertContains($invitee->identity(), ['participant1', 'participant2', 'participant3']);
        }
    }

    public function testRelocateCalendarEntry()
    {
        $calendarEntry = $this->calendarEntryAggregate();

        DomainRegistry::calendarEntryRepository()->save($calendarEntry);

        $this->calendarEntryApplicationService->relocateCalendarEntry(
            $calendarEntry->tenant()->id(),
            $calendarEntry->calendarEntryId()->id(),
            'A changed calendar entry location.'
        );

        $changedCalendarEntry = DomainRegistry::calendarEntryRepository()->calendarEntryOfId(
            $calendarEntry->tenant(),
            $calendarEntry->calendarEntryId()
        );

        $this->assertNotNull($changedCalendarEntry);
        $this->assertEquals('A changed calendar entry location.', $changedCalendarEntry->location());
    }

    public function testRescheduleCalendarEntry()
    {
        $calendarEntry = $this->calendarEntryAggregate();

        DomainRegistry::calendarEntryRepository()->save($calendarEntry);

        $now = new DateTimeImmutable();
        $nextWeek = $now->modify('+1 week');
        $nextWeekAndOneHour = $nextWeek->modify('+1 hour');

        $this->calendarEntryApplicationService->rescheduleCalendarEntry(
            $calendarEntry->tenant()->id(),
            $calendarEntry->calendarEntryId()->id(),
            'A changed description.',
            'A changed location.',
            $nextWeek,
            $nextWeekAndOneHour,
            'DoesNotRepeat',
            $nextWeekAndOneHour,
            'Hours',
            8
        );

        $changedCalendarEntry = DomainRegistry::calendarEntryRepository()->calendarEntryOfId(
            $calendarEntry->tenant(),
            $calendarEntry->calendarEntryId()
        );

        $this->assertNotNull($changedCalendarEntry);
        $this->assertEquals('A changed description.', $changedCalendarEntry->description());
        $this->assertEquals('A changed location.', $changedCalendarEntry->location());
        $this->assertEquals($nextWeek, $changedCalendarEntry->timeSpan()->begins());
        $this->assertEquals($nextWeekAndOneHour, $changedCalendarEntry->timeSpan()->ends());
        $this->assertTrue($changedCalendarEntry->repetition()->repeats()->isDoesNotRepeat());
        $this->assertTrue($changedCalendarEntry->alarm()->alarmUnitsType()->isHours());
        $this->assertEquals(8, $changedCalendarEntry->alarm()->alarmUnits());
    }

    public function testUninviteCalendarEntryParticipant()
    {
        $calendarEntry = $this->calendarEntryAggregate();

        DomainRegistry::calendarEntryRepository()->save($calendarEntry);

        $invitees = new ArrayCollection();
        $invitees->add('participant1');
        $invitees->add('participant2');
        $invitees->add('participant3');

        $this->calendarEntryApplicationService->inviteCalendarEntryParticipant(
            $calendarEntry->tenant()->id(),
            $calendarEntry->calendarEntryId()->id(),
            $invitees
        );

        $uninvitees = new ArrayCollection($invitees->toArray());
        $this->assertTrue($uninvitees->removeElement('participant2'));

        $this->calendarEntryApplicationService->uninviteCalendarEntryParticipant(
            $calendarEntry->tenant()->id(),
            $calendarEntry->calendarEntryId()->id(),
            $uninvitees
        );

        $changedCalendarEntry = DomainRegistry::calendarEntryRepository()->calendarEntryOfId(
            $calendarEntry->tenant(),
            $calendarEntry->calendarEntryId()
        );

        $this->assertNotNull($changedCalendarEntry);
        $this->assertEquals(1, $changedCalendarEntry->allInvitees()->count());
        $this->assertEquals('participant2', $changedCalendarEntry->allInvitees()->current()->identity());
    }
}
