<?php

namespace SaasOvation\Collaboration\Domain\Model\Calendar;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Icecave\Collections\Set;
use SaasOvation\Collaboration\Domain\Model\Collaborator\Owner;
use SaasOvation\Collaboration\Domain\Model\Tenant\Tenant;
use SaasOvation\Common\Domain\Model\EventSourcedRootEntity;

class Calendar extends EventSourcedRootEntity
{
    /**
     * @var CalendarId
     */
    private $calendarId;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $name;

    /**
     * @var Owner
     */
    private $owner;

    /**
     * @var Collection
     */
    private $sharedWith;

    /**
     * @var Tenant
     */
    private $tenant;

    private function createFrom(
        Tenant $aTenant,
        CalendarId $aCalendarId,
        $aName,
        $aDescription,
        Owner $anOwner,
        Collection $aSharedWith
    ) {
        $this->assertArgumentNotNull($aTenant, 'The tenant must be provided.');
        $this->assertArgumentNotNull($aCalendarId, 'The calendar id must be provided.');
        $this->assertArgumentNotEmpty($aName, 'The name must be provided.');
        $this->assertArgumentNotEmpty($aDescription, 'The description must be provided.');
        $this->assertArgumentNotNull($anOwner, 'The owner must be provided.');

        if (null === $aSharedWith) {
            $aSharedWith = new ArrayCollection();
        }

        $this->apply(
            new CalendarCreated($aTenant, $aCalendarId, $aName, $aDescription, $anOwner, $aSharedWith)
        );
    }

    public static function create(
        Tenant $aTenant,
        CalendarId $aCalendarId,
        $aName,
        $aDescription,
        Owner $anOwner,
        Collection $aSharedWith
    ) {
        $aCalendar = new Calendar();

        $aCalendar->createFrom(
            $aTenant,
            $aCalendarId,
            $aName,
            $aDescription,
            $anOwner,
            $aSharedWith
        );

        return $aCalendar;
    }

    public function allSharedWith()
    {
        return $this->sharedWith;
    }

    public function calendarId()
    {
        return $this->calendarId;
    }

    public function changeDescription($aDescription)
    {
        $this->assertArgumentNotEmpty($aDescription, 'The description must be provided.');

        $this->apply(
            new CalendarDescriptionChanged($this->tenant(), $this->calendarId(), $this->name(), $aDescription)
        );
    }

    public function description()
    {
        return $this->description;
    }

    public function name()
    {
        return $this->name;
    }

    public function owner()
    {
        return $this->owner;
    }

    public function rename($aName)
    {
        $this->assertArgumentNotEmpty($aName, 'The name must be provided.');

        $this->apply(
            new CalendarRenamed($this->tenant(), $this->calendarId(), $aName, $this->description())
        );
    }

    public function scheduleCalendarEntry(
        CalendarIdentityService $aCalendarIdentityService,
        $aDescription,
        $aLocation,
        Owner $anOwner,
        TimeSpan $aTimeSpan,
        Repetition $aRepetition,
        Alarm $anAlarm,
        ArrayCollection $anInvitees
    ) {

        return CalendarEntry::create(
            $this->tenant(),
            $this->calendarId(),
            $aCalendarIdentityService->nextCalendarEntryId(),
            $aDescription,
            $aLocation,
            $anOwner,
            $aTimeSpan,
            $aRepetition,
            $anAlarm,
            $anInvitees
        );
    }

    public function shareCalendarWith(CalendarSharer $aCalendarSharer)
    {
        $this->assertArgumentNotNull($aCalendarSharer, 'The calendar sharer must be provided.');

        $containsElement = $this->sharedWith()->exists(function($key, CalendarSharer $aCollectionCalendarSharer) use ($aCalendarSharer) {
            return $aCollectionCalendarSharer->equals($aCalendarSharer);
        });

        if (!$containsElement) {
            $this->apply(
                new CalendarShared($this->tenant(), $this->calendarId(), $this->name(), $aCalendarSharer)
            );
        }
    }

    public function unshareCalendarWith(CalendarSharer $aCalendarSharer)
    {
        $this->assertArgumentNotNull($aCalendarSharer, 'The calendar sharer must be provided.');

        $containsElement = $this->sharedWith()->exists(function($key, CalendarSharer $aCollectionCalendarSharer) use ($aCalendarSharer) {
            return $aCollectionCalendarSharer->equals($aCalendarSharer);
        });

        if ($containsElement) {
            $this->apply(
                new CalendarUnshared($this->tenant(), $this->calendarId(), $this->name(), $aCalendarSharer)
            );
        }
    }

    public function tenant()
    {
        return $this->tenant;
    }

    public function equals($anObject)
    {
        $equalObjects = false;

        if (null !== $anObject && get_class($this) === get_class($anObject)) {
            $equalObjects =
                $this->tenant()->equals($anObject->tenant())
                && $this->calendarId()->equals($anObject->calendarId());
        }

        return $equalObjects;
    }

    public function __toString()
    {
        return 'Calendar [calendarId=' . $this->calendarId . ', description=' . $this->description . ", name=" . $this->name . ", owner=" . $this->owner
        . ", sharedWith=" . $this->sharedWith . ", tenant=" . $this->tenant . "]";
    }

    protected function whenCalendarCreated(CalendarCreated $anEvent)
    {
        $this->setCalendarId($anEvent->calendarId());
        $this->setDescription($anEvent->description());
        $this->setName($anEvent->name());
        $this->setOwner($anEvent->owner());
        $this->setSharedWith(new ArrayCollection($anEvent->sharedWith()->toArray()));
        $this->setTenant($anEvent->tenant());
    }

    protected function whenCalendarDescriptionChanged(CalendarDescriptionChanged $anEvent)
    {
        $this->setDescription($anEvent->description());
    }

    protected function whenCalendarRenamed(CalendarRenamed $anEvent)
    {
        $this->setName($anEvent->name());
    }

    protected function whenCalendarShared(CalendarShared $anEvent)
    {
        $this->sharedWith()->add($anEvent->calendarSharer());
    }

    protected function whenCalendarUnshared(CalendarUnshared $anEvent)
    {
        $sharers = $this->sharedWith()->toArray();
        $indexToRemove = null;

        for ($i = 0; $i < count($sharers); $i++) {
            if ($anEvent->calendarSharer() == $sharers[$i]) {
                $indexToRemove = $i;
            }
        }

        if (null !== $indexToRemove) {
            unset($sharers[$indexToRemove]);
        }

        sort($sharers);
        $this->setSharedWith(new ArrayCollection($sharers));
    }

    private function setCalendarId(CalendarId $calendarId) {
        $this->calendarId = $calendarId;
    }

    private function setDescription($description)
    {
        $this->description = $description;
    }

    private function setName($name)
    {
        $this->name = $name;
    }

    private function setOwner(Owner $owner)
    {
        $this->owner = $owner;
    }

    private function sharedWith()
    {
        return $this->sharedWith;
    }

    private function setSharedWith($sharedWith)
    {
        $this->sharedWith = $sharedWith;
    }

    private function setTenant(Tenant $tenant)
    {
        $this->tenant = $tenant;
    }
}
