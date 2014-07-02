<?php

namespace SaasOvation\Collaboration\Test;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Rhumsaa\Uuid\Uuid;
use SaasOvation\Collaboration\Domain\Model\Calendar\Calendar;
use SaasOvation\Collaboration\Domain\Model\Calendar\CalendarEntry;
use SaasOvation\Collaboration\Domain\Model\Calendar\CalendarSharer;
use SaasOvation\Collaboration\Domain\Model\Calendar\Repetition;
use SaasOvation\Collaboration\Domain\Model\Collaborator\Author;
use SaasOvation\Collaboration\Domain\Model\Collaborator\Creator;
use SaasOvation\Collaboration\Domain\Model\Collaborator\Moderator;
use SaasOvation\Collaboration\Domain\Model\Collaborator\Owner;
use SaasOvation\Collaboration\Domain\Model\Collaborator\Participant;
use SaasOvation\Collaboration\Domain\Model\DomainRegistry;
use SaasOvation\Collaboration\Domain\Model\Forum\Discussion;
use SaasOvation\Collaboration\Domain\Model\Forum\Forum;
use SaasOvation\Collaboration\Domain\Model\Tenant\Tenant;

trait BuildsAggregates
{
    use ManipulatesDates;

    /**
     * @return Calendar
     */
    protected function calendarAggregate()
    {
        $tenant = new Tenant('01234567');

        return Calendar::create(
            $tenant,
            DomainRegistry::calendarRepository()->nextIdentity(),
            'John Doe\'s Calendar',
            'John Doe\'s everyday work calendar.',
            new Owner('jdoe', 'John Doe', 'jdoe@saasovation.com'),
            new ArrayCollection()
        );
    }

    /**
     * @return CalendarEntry
     */
    protected function calendarEntryAggregate()
    {
        $calendar = $this->calendarAggregate();

        DomainRegistry::calendarRepository()->save($calendar);

        $calendarEntry = $calendar->scheduleCalendarEntry(
            DomainRegistry::calendarIdentityService(),
            'A Doctor Checkup.',
            'Family Practice Offices',
            new Owner('jdoe', 'John Doe', 'jdoe@saasovation.com'),
            $this->tomorrowOneHourTimeSpan(),
            $this->weeklyRepetition(),
            $this->oneHourBeforeAlarm(),
            new ArrayCollection()
        );

        return $calendarEntry;
    }

    protected function calendarEntryAggregates()
    {
        $calendar = $this->calendarAggregate();

        DomainRegistry::calendarRepository()->save($calendar);

        $invitees = new ArrayCollection();
        $invitees->add(new Participant('zoe', 'Zoe Doe', 'zoe@saasovation.com'));

        $calendarEntry1 = $calendar->scheduleCalendarEntry(
            DomainRegistry::calendarIdentityService(),
            'A Doctor Checkup',
            'Family Practice Offices',
            new Owner('jdoe', 'John Doe', 'jdoe@saasovation.com'),
            $this->daysFromNowOneHourTimeSpan(1),
            Repetition::doesNotRepeatInstance(new DateTimeImmutable()),
            $this->oneHourBeforeAlarm(),
            $invitees
        );

        $calendarEntry2 = $calendar->scheduleCalendarEntry(
            DomainRegistry::calendarIdentityService(),
            'A Break Replacement',
            'Breaks R Us',
            new Owner('jdoe', 'John Doe', 'jdoe@saasovation.com'),
            $this->daysFromNowOneHourTimeSpan(2),
            Repetition::doesNotRepeatInstance(new DateTimeImmutable()),
            $this->oneHourBeforeAlarm(),
            $invitees
        );

        $calendarEntry3 = $calendar->scheduleCalendarEntry(
            DomainRegistry::calendarIdentityService(),
            'Dinner with Family',
            'Burritos Grandes',
            new Owner('jdoe', 'John Doe', 'jdoe@saasovation.com'),
            $this->daysFromNowOneHourTimeSpan(3),
            Repetition::doesNotRepeatInstance(new DateTimeImmutable()),
            $this->oneHourBeforeAlarm(),
            $invitees
        );

        return [ $calendarEntry1, $calendarEntry2, $calendarEntry3 ];
    }

    protected function calendarAggregates()
    {
        $tenant = new Tenant('01234567');

        $invitees = new ArrayCollection();
        $invitees->add(new CalendarSharer(new Participant('zoe', 'Zoe Doe', 'zoe@saasovation.com')));

        $calendar1 = Calendar::create(
            $tenant,
            DomainRegistry::calendarRepository()->nextIdentity(),
            'John Doe\'s Calendar',
            'John Doe\'s everyday work calendar.',
            new Owner('jdoe', 'John Doe', 'jdoe@saasovation.com'),
            $invitees
        );

        $calendar2 = Calendar::create(
            $tenant,
            DomainRegistry::calendarRepository()->nextIdentity(),
            'Zoe Doe\'s Calendar',
            'Zoe Doe\'s awesome person calendar.',
            new Owner('zoe', 'Zoe Doe', 'zoe@saasovation.com'),
            $invitees
        );

        $calendar3 = Calendar::create(
            $tenant,
            DomainRegistry::calendarRepository()->nextIdentity(),
            'Joe Smith\'s Calendar',
            'Joe Smith\'s know-everything calendar.',
            new Owner('joe', 'Joe Smith', 'joe@saasovation.com'),
            $invitees
        );

        return [ $calendar1, $calendar2, $calendar3 ];
    }

    protected function discussionAggregate(Forum $aForum)
    {
        $discussion = $aForum->startDiscussionFor(
            DomainRegistry::forumIdentityService(),
            new Author('jdoe', 'John Doe', 'jdoe@saasovation.com'),
            'All About DDD',
            strtoupper(Uuid::uuid4())
        );

        return $discussion;
    }

    protected function discussionAggregates(Forum $aForum)
    {
        $discussion1 = $aForum->startDiscussionFor(
            DomainRegistry::forumIdentityService(),
            new Author('jdoe', 'John Doe', 'jdoe@saasovation.com'),
            'All About DDD',
            strtoupper(Uuid::uuid4())
        );

        $discussion2 = $aForum->startDiscussionFor(
            DomainRegistry::forumIdentityService(),
            new Author('zoe', 'Zoe Doe', 'zoe@saasovation.com'),
            'I Already Know That, Too',
            strtoupper(Uuid::uuid4())
        );

        $discussion3 = $aForum->startDiscussionFor(
            DomainRegistry::forumIdentityService(),
            new Author('joe', 'Joe Smith', 'joe@saasovation.com'),
            'I\'ve Forgotten More Than Zoe Knows',
            strtoupper(Uuid::uuid4())
        );

        return [ $discussion1, $discussion2, $discussion3 ];
    }

    protected function forumAggregate()
    {
        return Forum::create(
            new Tenant('01234567'),
            DomainRegistry::forumRepository()->nextIdentity(),
            new Creator('jdoe', 'John Doe', 'jdoe@saasovation.com'),
            new Moderator('jdoe', 'John Doe', 'jdoe@saasovation.com'),
            'John Doe Does DDD',
            'A set of discussions about DDD for anonymous developers.',
            strtoupper(Uuid::uuid4())
        );
    }

    protected function forumAggregates()
    {
        $tenant = new Tenant('01234567');

        $forum1 = Forum::create(
            $tenant,
            DomainRegistry::forumRepository()->nextIdentity(),
            new Creator('jdoe', 'John Doe', 'jdoe@saasovation.com'),
            new Moderator('jdoe', 'John Doe', 'jdoe@saasovation.com'),
            'John Doe Does DDD',
            'A set of discussions about DDD for anonymous developers.',
            strtoupper(Uuid::uuid4())
        );

        $forum2 = Forum::create(
            $tenant,
            DomainRegistry::forumRepository()->nextIdentity(),
            new Creator('zdoe', 'Zoe Doe', 'zdoe@saasovation.com'),
            new Moderator('zdoe', 'Zoe Doe', 'zdoe@saasovation.com'),
            'Zoe Doe Knows DDD',
            'Discussions about how ubiquitous Zoe\'s knows is.',
            strtoupper(Uuid::uuid4())
        );

        $forum3 = Forum::create(
            $tenant,
            DomainRegistry::forumRepository()->nextIdentity(),
            new Creator('joe', 'Joe Smith', 'joe@saasovation.com'),
            new Moderator('joe', 'Joe Smith', 'joe@saasovation.com'),
            'Joe Owns DDD',
            'Discussions about Joe\'s Values.',
            strtoupper(Uuid::uuid4())
        );

        return [ $forum1, $forum2, $forum3 ];
    }

    protected function postAggregate(Discussion $aDiscussion)
    {
        return $aDiscussion->postWithoutReply(
            DomainRegistry::forumIdentityService(),
            new Author('jdoe', 'John Doe', 'jdoe@saasovation.com'),
            'I Am All About DDD',
            'That\'s what I\'m talk\'n about.'
        );
    }

    protected function postAggregates(Discussion $aDiscussion)
    {
        $post1 = $aDiscussion->postWithoutReply(
            DomainRegistry::forumIdentityService(),
            new Author('jdoe', 'John Doe', 'jdoe@saasovation.com'),
            'I Am All About DDD',
            'That\'s what I\'m talk\'n about.'
        );

        $post2 = $aDiscussion->postWithoutReply(
            DomainRegistry::forumIdentityService(),
            new Author('zoe', 'Zoe Doe', 'zoe@saasovation.com'),
            'RE: I Am All About DDD',
            'No, no, no. That\'s what *I\'m* talk\'n about.');

        $post3 = $aDiscussion->postWithoutReply(
            DomainRegistry::forumIdentityService(),
            new Author('joe', 'Joe Smith', 'joe@saasovation.com'),
            'RE: I Am All About DDD',
            'Did I mention that I\'ve forgotten more than you will ever know?'
        );

        return [ $post1, $post2, $post3 ];
    }
}