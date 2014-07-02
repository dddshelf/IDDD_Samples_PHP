<?php

namespace SaasOvation\Collaboration\Test\Domain\Model\Calendar;

use SaasOvation\Collaboration\Domain\Model\Calendar\CalendarCreated;
use SaasOvation\Collaboration\Domain\Model\Calendar\CalendarDescriptionChanged;
use SaasOvation\Collaboration\Domain\Model\Calendar\CalendarEntryDescriptionChanged;
use SaasOvation\Collaboration\Domain\Model\Calendar\CalendarEntryParticipantInvited;
use SaasOvation\Collaboration\Domain\Model\Calendar\CalendarEntryParticipantUninvited;
use SaasOvation\Collaboration\Domain\Model\Calendar\CalendarEntryRelocated;
use SaasOvation\Collaboration\Domain\Model\Calendar\CalendarEntryRescheduled;
use SaasOvation\Collaboration\Domain\Model\Calendar\CalendarEntryScheduled;
use SaasOvation\Collaboration\Domain\Model\Calendar\CalendarRenamed;
use SaasOvation\Collaboration\Domain\Model\Calendar\CalendarShared;
use SaasOvation\Collaboration\Domain\Model\Calendar\CalendarSharer;
use SaasOvation\Collaboration\Domain\Model\Calendar\CalendarUnshared;
use SaasOvation\Collaboration\Domain\Model\Calendar\RepeatType;
use SaasOvation\Collaboration\Domain\Model\Calendar\Repetition;
use SaasOvation\Collaboration\Domain\Model\DomainRegistry;
use SaasOvation\Collaboration\Test\Domain\Model\DomainTest;
use SaasOvation\Collaboration\Domain\Model\Collaborator\Participant;
use SaasOvation\Common\Domain\Model\DomainEvent;
use SaasOvation\Common\Domain\Model\DomainEventPublisher;
use SaasOvation\Common\Domain\Model\DomainEventSubscriber;

class CalendarTest extends DomainTest
{
    /**
     * @test
     */
    public function createCalendar()
    {
        $calendar = $this->calendarAggregate();

        $this->assertEquals('John Doe\'s Calendar', $calendar->name());
        $this->assertEquals('John Doe\'s everyday work calendar.', $calendar->description());
        $this->assertEquals('jdoe', $calendar->owner()->identity());

        DomainRegistry::calendarRepository()->save($calendar);

        $this->expectedEvents(1);
        $this->expectedEvent(CalendarCreated::class);

        $this->collaborationRabbitMQExchangeListener->listenForPendingMessages();

        $this->expectedNotifications(1);
        $this->expectedNotification(CalendarCreated::class);
    }

    /**
     * @test
     */
    public function calendarChangeDescription()
    {
        $calendar = $this->calendarAggregate();

        $calendar->changeDescription('A changed description.');

        $this->assertEquals('A changed description.', $calendar->description());

        DomainRegistry::calendarRepository()->save($calendar);

        $this->expectedEvents(2);
        $this->expectedEvent(CalendarCreated::class);
        $this->expectedEvent(CalendarDescriptionChanged::class);

        $this->collaborationRabbitMQExchangeListener->listenForPendingMessages(2);

        $this->expectedNotifications(2);
        $this->expectedNotification(CalendarCreated::class);
        $this->expectedNotification(CalendarDescriptionChanged::class);
    }

    /**
     * @test
     */
    public function renameCalendar()
    {
        $calendar = $this->calendarAggregate();

        $calendar->rename('A different name.');

        $this->assertEquals('A different name.', $calendar->name());

        DomainRegistry::calendarRepository()->save($calendar);

        $this->expectedEvents(2);
        $this->expectedEvent(CalendarCreated::class);
        $this->expectedEvent(CalendarRenamed::class);

        $this->collaborationRabbitMQExchangeListener->listenForPendingMessages(2);

        $this->expectedNotifications(2);
        $this->expectedNotification(CalendarCreated::class);
        $this->expectedNotification(CalendarRenamed::class);
    }

    /**
     * @test
     */
    public function calendarSharesWithUnshare()
    {
        $calendar = $this->calendarAggregate();

        $this->assertTrue($calendar->allSharedWith()->isEmpty());

        $calendar->shareCalendarWith(
            new CalendarSharer(
                new Participant('zdoe', 'Zoe Doe', 'zdoe@saasovation.com')
            )
        );

        $calendar->shareCalendarWith(
            new CalendarSharer(
                new Participant('jdoe', 'John Doe', 'jdoe@saasovation.com')
            )
        );

        $this->assertFalse($calendar->allSharedWith()->isEmpty());

        $sharer = $calendar->allSharedWith()->getIterator()->current();

        $calendar->unshareCalendarWith($sharer);

        $this->assertFalse($calendar->allSharedWith()->isEmpty());

        DomainRegistry::calendarRepository()->save($calendar);

        $this->expectedEvents(4);
        $this->expectedEvent(CalendarCreated::class);
        $this->expectedEvent(CalendarShared::class, 2);
        $this->expectedEvent(CalendarUnshared::class);

        $this->collaborationRabbitMQExchangeListener->listenForPendingMessages(4);

        $this->expectedNotifications(4);
        $this->expectedNotification(CalendarCreated::class);
        $this->expectedNotification(CalendarShared::class, 2);
        $this->expectedNotification(CalendarUnshared::class);
    }

    /**
     * @test
     */
    public function calendarShares()
    {
        $calendar = $this->calendarAggregate();

        $this->assertTrue($calendar->allSharedWith()->isEmpty());

        $calendar->shareCalendarWith(
            new CalendarSharer(
                new Participant('zdoe', 'Zoe Doe', 'zdoe@saasovation.com')
            )
        );

        $calendar->shareCalendarWith(
            new CalendarSharer(
                new Participant('jdoe', 'John Doe', 'jdoe@saasovation.com')
            )
        );

        $this->assertFalse($calendar->allSharedWith()->isEmpty());

        DomainRegistry::calendarRepository()->save($calendar);

        $this->expectedEvents(3);
        $this->expectedEvent(CalendarCreated::class);
        $this->expectedEvent(CalendarShared::class, 2);

        $this->collaborationRabbitMQExchangeListener->listenForPendingMessages(3);

        $this->expectedNotifications(3);
        $this->expectedNotification(CalendarCreated::class);
        $this->expectedNotification(CalendarShared::class, 2);
    }

    /**
     * @test
     */
    public function scheduleCalendarEntry()
    {
        $aSubscriber = new CalendarEntryScheduledSubscriber();

        DomainEventPublisher::instance()->subscribe($aSubscriber);

        $calendarEntry = $this->calendarEntryAggregate();

        DomainRegistry::calendarEntryRepository()->save($calendarEntry);

        $this->assertNotNull($aSubscriber->getCalendarEntryId());

        $this->expectedEvents(2);
        $this->expectedEvent(CalendarCreated::class);
        $this->expectedEvent(CalendarEntryScheduled::class);

        $this->collaborationRabbitMQExchangeListener->listenForPendingMessages(2);

        $this->expectedNotifications(2);
        $this->expectedNotification(CalendarCreated::class);
        $this->expectedNotification(CalendarEntryScheduled::class);
    }

    /**
     * @test
     */
    public function calendarEntryChangeDescription()
    {
        $calendarEntry = $this->calendarEntryAggregate();

        $calendarEntry->changeDescription('A changed description.');

        $this->assertEquals('A changed description.', $calendarEntry->description());

        DomainRegistry::calendarEntryRepository()->save($calendarEntry);

        $this->expectedEvents(3);
        $this->expectedEvent(CalendarCreated::class);
        $this->expectedEvent(CalendarEntryScheduled::class);
        $this->expectedEvent(CalendarEntryDescriptionChanged::class);

        $this->collaborationRabbitMQExchangeListener->listenForPendingMessages(3);

        $this->expectedNotifications(3);
        $this->expectedNotification(CalendarCreated::class);
        $this->expectedNotification(CalendarEntryScheduled::class);
        $this->expectedNotification(CalendarEntryDescriptionChanged::class);
    }

    /**
     * @test
     */
    public function inviteToCalendarEntry()
    {
        $calendarEntry = $this->calendarEntryAggregate();

        $this->assertTrue($calendarEntry->allInvitees()->isEmpty());

        $invitee1 = new Participant('jdoe', 'John Doe', 'jdoe@saasovation.com');

        $calendarEntry->invite($invitee1);

        $this->assertFalse($calendarEntry->allInvitees()->isEmpty());
        $this->assertEquals(1, $calendarEntry->allInvitees()->count());
        $this->assertEquals($invitee1, $calendarEntry->allInvitees()->getIterator()->current());

        $calendarEntry->uninvite($invitee1);

        $this->assertTrue($calendarEntry->allInvitees()->isEmpty());

        $invitee2 = new Participant('tsmith', 'Tom Smith', 'tsmith@saasovation.com');

        $calendarEntry->invite($invitee1);
        $calendarEntry->invite($invitee2);

        $this->assertFalse($calendarEntry->allInvitees()->isEmpty());
        $this->assertEquals(2, $calendarEntry->allInvitees()->count());

        $iterator = $calendarEntry->allInvitees();
        $participant1 = $iterator->first();
        $participant2 = $iterator->next();

        $this->assertContains($participant1, [$invitee1, $invitee2]);
        $this->assertContains($participant2, [$invitee1, $invitee2]);

        $calendarEntry->uninvite($invitee1);

        $this->assertFalse($calendarEntry->allInvitees()->isEmpty());

        $calendarEntry->uninvite($invitee2);

        $this->assertTrue($calendarEntry->allInvitees()->isEmpty());

        DomainRegistry::calendarEntryRepository()->save($calendarEntry);

        $this->expectedEvents(8);
        $this->expectedEvent(CalendarCreated::class);
        $this->expectedEvent(CalendarEntryScheduled::class);
        $this->expectedEvent(CalendarEntryParticipantInvited::class, 3);
        $this->expectedEvent(CalendarEntryParticipantUninvited::class, 3);

        $this->collaborationRabbitMQExchangeListener->listenForPendingMessages(8);

        $this->expectedNotifications(8);
        $this->expectedNotification(CalendarCreated::class);
        $this->expectedNotification(CalendarEntryScheduled::class);
        $this->expectedNotification(CalendarEntryParticipantInvited::class, 3);
        $this->expectedNotification(CalendarEntryParticipantUninvited::class, 3);
    }

    /**
     * @test
     */
    public function relocateCaledarEntry()
    {
        $calendarEntry = $this->calendarEntryAggregate();

        $calendarEntry->relocate('A changed location.');

        $this->assertEquals('A changed location.', $calendarEntry->location());

        DomainRegistry::calendarEntryRepository()->save($calendarEntry);

        $this->expectedEvents(3);
        $this->expectedEvent(CalendarCreated::class);
        $this->expectedEvent(CalendarEntryScheduled::class);
        $this->expectedEvent(CalendarEntryRelocated::class);

        $this->collaborationRabbitMQExchangeListener->listenForPendingMessages(3);

        $this->expectedNotifications(3);
        $this->expectedNotification(CalendarCreated::class);
        $this->expectedNotification(CalendarEntryScheduled::class);
        $this->expectedNotification(CalendarEntryRelocated::class);
    }

    /**
     * @test
     */
    public function rescheduleCalendarEntry()
    {
        $calendarEntry = $this->calendarEntryAggregate();

        $timeSpan = $this->oneWeekAroundTimeSpan();
        $repetition = Repetition::doesNotRepeatInstance($timeSpan->ends());

        $calendarEntry->reschedule(
            'A changed description.',
            'A changed location.',
            $timeSpan,
            $repetition,
            $this->oneHourBeforeAlarm()
        );

        $this->assertEquals('A changed description.', $calendarEntry->description());
        $this->assertEquals('A changed location.', $calendarEntry->location());
        $this->assertEquals($this->oneWeekAroundTimeSpan(), $calendarEntry->timeSpan());
        $this->assertEquals($repetition, $calendarEntry->repetition());
        $this->assertEquals($this->oneHourBeforeAlarm(), $calendarEntry->alarm());

        $calendarEntry->reschedule(
            'A changed description.',
            'A changed location.',
            $this->oneWeekAroundTimeSpan(),
            Repetition::indefinitelyRepeatsInstance(new RepeatType\Weekly()),
            $this->oneHourBeforeAlarm()
        );

        $this->assertEquals('A changed description.', $calendarEntry->description());
        $this->assertEquals('A changed location.', $calendarEntry->location());
        $this->assertEquals($this->oneWeekAroundTimeSpan(), $calendarEntry->timeSpan());
        $this->assertEquals(Repetition::indefinitelyRepeatsInstance(new RepeatType\Weekly()), $calendarEntry->repetition());
        $this->assertEquals($this->oneHourBeforeAlarm(), $calendarEntry->alarm());

        DomainRegistry::calendarEntryRepository()->save($calendarEntry);

        $this->expectedEvents(6);
        $this->expectedEvent(CalendarCreated::class);
        $this->expectedEvent(CalendarEntryScheduled::class);
        $this->expectedEvent(CalendarEntryDescriptionChanged::class, 1);
        $this->expectedEvent(CalendarEntryRelocated::class, 1);
        $this->expectedEvent(CalendarEntryRescheduled::class, 2);

        $this->collaborationRabbitMQExchangeListener->listenForPendingMessages(6);

        $this->expectedNotifications(6);
        $this->expectedNotification(CalendarCreated::class);
        $this->expectedNotification(CalendarEntryScheduled::class);
        $this->expectedNotification(CalendarEntryDescriptionChanged::class);
        $this->expectedNotification(CalendarEntryRelocated::class, 1);
        $this->expectedNotification(CalendarEntryRescheduled::class, 2);
    }
}
