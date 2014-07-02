<?php

namespace SaasOvation\Collaboration\Domain\Model\Calendar;

use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Icecave\Collections\Set;
use SaasOvation\Collaboration\Domain\Model\Collaborator\Owner;
use SaasOvation\Collaboration\Domain\Model\Collaborator\Participant;
use SaasOvation\Collaboration\Domain\Model\Tenant\Tenant;
use SaasOvation\Common\Domain\Model\EventSourcedRootEntity;

class CalendarEntry extends EventSourcedRootEntity
{
    /**
     * @var Alarm
     */
    private $alarm;

    /**
     * @var CalendarEntryId
     */
    private $calendarEntryId;

    /**
     * @var CalendarId
     */
    private $calendarId;

    /**
     * @var string
     */
    private $description;

    /**
     * @var Collection
     */
    private $invitees;

    /**
     * @var string
     */
    private $location;

    /**
     * @var Owner
     */
    private $owner;

    /**
     * @var Repetition
     */
    private $repetition;

    /**
     * @var Tenant
     */
    private $tenant;

    /**
     * @var TimeSpan
     */
    private $timeSpan;

    public function alarm()
    {
        return $this->alarm;
    }

    public function allInvitees()
    {
        return $this->invitees();
    }

    public function calendarEntryId()
    {
        return $this->calendarEntryId;
    }

    public function calendarId()
    {
        return $this->calendarId;
    }

    public function description()
    {
        return $this->description;
    }

    public function location()
    {
        return $this->location;
    }

    public function owner()
    {
        return $this->owner;
    }

    public function repetition()
    {
        return $this->repetition;
    }

    public function changeDescription($aDescription)
    {
        if (null !== $aDescription) {
            $aDescription = trim($aDescription);

            if (!empty($aDescription) && $aDescription !== $this->description()) {
                $this->apply(
                    new CalendarEntryDescriptionChanged($this->tenant(), $this->calendarId(), $this->calendarEntryId(), $aDescription)
                );
            }
        }
    }

    public function invite(Participant $aParticipant)
    {
        $this->assertArgumentNotNull($aParticipant, 'The participant must be provided.');

        $containsElement = $this->invitees()->exists(function($key, Participant $aCollectionParticipant) use ($aParticipant) {
            return $aCollectionParticipant->equals($aParticipant);
        });

        if (!$containsElement) {
            $this->apply(
                new CalendarEntryParticipantInvited($this->tenant(), $this->calendarId(), $this->calendarEntryId(), $aParticipant)
            );
        }
    }

    public function relocate($aLocation)
    {
        if (null !== $aLocation) {
            $aLocation = trim($aLocation);

            if (!empty($aLocation) && $aLocation !== $this->location()) {
                $this->apply(
                    new CalendarEntryRelocated($this->tenant(), $this->calendarId(), $this->calendarEntryId(), $aLocation)
                );
            }
        }
    }

    public function reschedule(
        $aDescription,
        $aLocation,
        TimeSpan $aTimeSpan,
        Repetition $aRepetition,
        Alarm $anAlarm
    ) {

        $this->assertArgumentNotNull($anAlarm, 'The alarm must be provided.');
        $this->assertArgumentNotNull($aRepetition, 'The repetition must be provided.');
        $this->assertArgumentNotNull($aTimeSpan, 'The time span must be provided.');

        if ($aRepetition->repeats()->isDoesNotRepeat()) {
            $aRepetition = Repetition::doesNotRepeatInstance($aTimeSpan->ends());
        }

        $this->assertTimeSpans($aRepetition, $aTimeSpan);

        $this->changeDescription($aDescription);
        $this->relocate($aLocation);

        $this->apply(
            new CalendarEntryRescheduled($this->tenant(), $this->calendarId(), $this->calendarEntryId(), $aTimeSpan, $aRepetition, $anAlarm)
        );
    }

    public function tenant()
    {
        return $this->tenant;
    }

    public function timeSpan()
    {
        return $this->timeSpan;
    }

    public function uninvite(Participant $aParticipant)
    {
        $this->assertArgumentNotNull($aParticipant, 'The participant must be provided.');

        $containsElement = $this->invitees()->exists(function($key, Participant $aCollectionParticipant) use ($aParticipant) {
            return $aCollectionParticipant->equals($aParticipant);
        });

        if ($containsElement) {
            $this->apply(
                new CalendarEntryParticipantUninvited($this->tenant(), $this->calendarId(), $this->calendarEntryId(), $aParticipant)
            );
        }
    }

    public function equals($anObject)
    {
        $equalObjects = false;

        if (null !== $anObject && get_class($this) === get_class($anObject)) {
            $equalObjects =
                $this->tenant()->equals($anObject->tenant()) &&
                $this->calendarId()->equals($anObject->calendarId()) &&
                $this->calendarEntryId()->equals($anObject->calendarEntryId());
        }

        return $equalObjects;
    }

    public function __toString()
    {
        return "CalendarEntry [alarm=" . $this->alarm . ", calendarEntryId=" . $this->calendarEntryId . ", calendarId=" . $this->calendarId
        . ", description=" . $this->description . ", invitees=" . $this->invitees . ", location=" . $this->location . ", owner=" . $this->owner
        . ", repetition=" . $this->repetition . ", tenant=" . $this->tenant . ", timeSpan=" . $this->timeSpan . "]";
    }

    private function createFrom(
        Tenant $aTenant,
        CalendarId $aCalendarId,
        CalendarEntryId $aCalendarEntryId,
        $aDescription,
        $aLocation,
        Owner $anOwner,
        TimeSpan $aTimeSpan,
        Repetition $aRepetition,
        Alarm $anAlarm,
        Collection $anInvitees
    ) {

        $this->assertArgumentNotNull($anAlarm, "The alarm must be provided.");
        $this->assertArgumentNotNull($aCalendarEntryId, "The calendar entry id must be provided.");
        $this->assertArgumentNotNull($aCalendarId, "The calendar id must be provided.");
        $this->assertArgumentNotEmpty($aDescription, "The description must be provided.");
        $this->assertArgumentNotEmpty($aLocation, "The location must be provided.");
        $this->assertArgumentNotNull($anOwner, "The owner must be provided.");
        $this->assertArgumentNotNull($aRepetition, "The repetition must be provided.");
        $this->assertArgumentNotNull($aTenant, "The tenant must be provided.");
        $this->assertArgumentNotNull($aTimeSpan, "The time span must be provided.");

        if ($aRepetition->repeats()->isDoesNotRepeat()) {
            $aRepetition = Repetition::doesNotRepeatInstance($aTimeSpan->ends());
        }

        $this->assertTimeSpans($aRepetition, $aTimeSpan);

        if (null === $anInvitees) {
            $anInvitees = new ArrayCollection();
        }

        $this->apply(
            new CalendarEntryScheduled($aTenant, $aCalendarId, $aCalendarEntryId, $aDescription, $aLocation, $anOwner, $aTimeSpan, $aRepetition, $anAlarm, $anInvitees)
        );
    }

    public static function create(
        Tenant $aTenant,
        CalendarId $aCalendarId,
        CalendarEntryId $aCalendarEntryId,
        $aDescription,
        $aLocation,
        Owner $anOwner,
        TimeSpan $aTimeSpan,
        Repetition $aRepetition,
        Alarm $anAlarm,
        Collection $anInvitees
    ) {
        $calendarEntry = new CalendarEntry();

        $calendarEntry->createFrom(
            $aTenant,
            $aCalendarId,
            $aCalendarEntryId,
            $aDescription,
            $aLocation,
            $anOwner,
            $aTimeSpan,
            $aRepetition,
            $anAlarm,
            $anInvitees
        );

        return $calendarEntry;
    }

    protected function whenCalendarEntryDescriptionChanged(CalendarEntryDescriptionChanged $anEvent)
    {
        $this->setDescription($anEvent->description());
    }

    protected function whenCalendarEntryParticipantInvited(CalendarEntryParticipantInvited $anEvent)
    {
        $this->invitees()->add($anEvent->participant());
    }

    protected function whenCalendarEntryRelocated(CalendarEntryRelocated $anEvent)
    {
        $this->setLocation($anEvent->location());
    }

    protected function whenCalendarEntryRescheduled(CalendarEntryRescheduled $anEvent)
    {
        $this->setAlarm($anEvent->alarm());
        $this->setRepetition($anEvent->repetition());
        $this->setTimeSpan($anEvent->timeSpan());
    }

    protected function whenCalendarEntryScheduled(CalendarEntryScheduled $anEvent)
    {
        $this->setAlarm($anEvent->alarm());
        $this->setCalendarEntryId($anEvent->calendarEntryId());
        $this->setCalendarId($anEvent->calendarId());
        $this->setDescription($anEvent->description());
        $this->setInvitees($anEvent->invitees());
        $this->setLocation($anEvent->location());
        $this->setOwner($anEvent->owner());
        $this->setRepetition($anEvent->repetition());
        $this->setTenant($anEvent->tenant());
        $this->setTimeSpan($anEvent->timeSpan());
    }

    protected function whenCalendarEntryParticipantUninvited(CalendarEntryParticipantUninvited $anEvent)
    {
        $invitees = array_filter(
            $this->invitees()->toArray(),
            function (Participant $aParticipant) use ($anEvent) {
                return !$aParticipant->equals($anEvent->participant());
            }
        );

        $this->setInvitees(new ArrayCollection($invitees));
    }

    private function setAlarm(Alarm $anAlarm)
    {
        $this->alarm = $anAlarm;
    }

    private function assertTimeSpans(Repetition $aRepetition, TimeSpan $aTimeSpan)
    {
        if ($aRepetition->repeats()->isDoesNotRepeat()) {
            $this->assertArgumentEquals(
                $aTimeSpan->ends(),
                $aRepetition->ends(),
                'Non-repeating entry must end with time span end.'
            );
        } else {
            $this->assertArgumentFalse(
                $aTimeSpan->ends() > $aRepetition->ends(),
                'Time span must end when or before repetition ends.'
            );
        }
    }

    private function setCalendarEntryId(CalendarEntryId $aCalendarEntryId)
    {
        $this->calendarEntryId = $aCalendarEntryId;
    }

    private function setCalendarId(CalendarId $aCalendarId)
    {
        $this->calendarId = $aCalendarId;
    }

    private function setDescription($aDescription)
    {
        $this->description = $aDescription;
    }

    private function invitees()
    {
        return $this->invitees;
    }

    private function setInvitees(Collection $anInvitees)
    {
        $this->invitees = $anInvitees;
    }

    private function setLocation($aLocation)
    {
        $this->location = $aLocation;
    }

    private function setOwner(Owner $anOwner)
    {
        $this->owner = $anOwner;
    }

    private function setRepetition(Repetition $aRepetition)
    {
        $this->repetition = $aRepetition;
    }

    private function setTenant(Tenant $aTenant)
    {
        $this->tenant = $aTenant;
    }

    private function setTimeSpan(TimeSpan $aTimeSpan)
    {
        $this->timeSpan = $aTimeSpan;
    }
}
