<?php

namespace SaasOvation\Collaboration\Application\Calendar;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use SaasOvation\Collaboration\Application\Calendar\Data\CalendarCommandResult;
use SaasOvation\Collaboration\Domain\Model\Calendar\Alarm;
use SaasOvation\Collaboration\Domain\Model\Calendar\AlarmUnitsType;
use SaasOvation\Collaboration\Domain\Model\Calendar\Calendar;
use SaasOvation\Collaboration\Domain\Model\Calendar\CalendarEntryRepository;
use SaasOvation\Collaboration\Domain\Model\Calendar\CalendarId;
use SaasOvation\Collaboration\Domain\Model\Calendar\CalendarIdentityService;
use SaasOvation\Collaboration\Domain\Model\Calendar\CalendarRepository;
use SaasOvation\Collaboration\Domain\Model\Calendar\CalendarSharer;
use SaasOvation\Collaboration\Domain\Model\Calendar\RepeatType;
use SaasOvation\Collaboration\Domain\Model\Calendar\Repetition;
use SaasOvation\Collaboration\Domain\Model\Calendar\TimeSpan;
use SaasOvation\Collaboration\Domain\Model\Collaborator\CollaboratorService;
use SaasOvation\Collaboration\Domain\Model\Tenant\Tenant;

class CalendarApplicationService
{
    /**
     * @var CalendarRepository
     */
    private $calendarRepository;

    /**
     * @var CalendarEntryRepository
     */
    private $calendarEntryRepository;

    /**
     * @var CalendarIdentityService
     */
    private $calendarIdentityService;

    /**
     * @var CollaboratorService
     */
    private $collaboratorService;

    public function __construct(
        CalendarRepository $aCalendarRepository,
        CalendarEntryRepository $aCalendarEntryRepository,
        CalendarIdentityService $aCalendarIdentityService,
        CollaboratorService $aCollaboratorService
    ) {
        $this->calendarRepository = $aCalendarRepository;
        $this->calendarEntryRepository = $aCalendarEntryRepository;
        $this->calendarIdentityService = $aCalendarIdentityService;
        $this->collaboratorService = $aCollaboratorService;
    }

    public function changeCalendarDescription(
        $aTenantId,
        $aCalendarId,
        $aDescription
    ) {

        $tenant = new Tenant($aTenantId);

        $calendar = $this->calendarRepository()->calendarOfId(
            $tenant,
            new CalendarId($aCalendarId)
        );

        $calendar->changeDescription($aDescription);

        $this->calendarRepository()->save($calendar);
    }

    public function createCalendar(
        $aTenantId,
        $aName,
        $aDescription,
        $anOwnerId,
        Collection $aParticipantsToSharedWith,
        CalendarCommandResult $aCalendarCommandResult
    ) {

        $tenant = new Tenant($aTenantId);

        $owner = $this->collaboratorService()->ownerFrom($tenant, $anOwnerId);

        $sharers = $this->sharersFrom($tenant, $aParticipantsToSharedWith);

        $calendar = Calendar::create(
            $tenant,
            $this->calendarRepository->nextIdentity(),
            $aName,
            $aDescription,
            $owner,
            $sharers
        );

        $this->calendarRepository()->save($calendar);

        $aCalendarCommandResult->resultingCalendarId($calendar->calendarId()->id());
    }

    public function renameCalendar(
        $aTenantId,
        $aCalendarId,
        $aName
    ) {

        $tenant = new Tenant($aTenantId);

        $calendar = $this->calendarRepository()->calendarOfId(
            $tenant,
            new CalendarId($aCalendarId)
        );

        $calendar->rename($aName);

        $this->calendarRepository()->save($calendar);
    }

    public function scheduleCalendarEntry(
        $aTenantId,
        $aCalendarId,
        $aDescription,
        $aLocation,
        $anOwnerId,
        DateTimeInterface $aTimeSpanBegins,
        DateTimeInterface $aTimeSpanEnds,
        $aRepeatType,
        DateTimeInterface $aRepeatEndsOnDate,
        $anAlarmType,
        $anAlarmUnits,
        Collection $aParticipantsToInvite,
        CalendarCommandResult $aCalendarCommandResult
    ) {

        $tenant = new Tenant($aTenantId);

        $calendar = $this->calendarRepository()->calendarOfId(
            $tenant,
            new CalendarId($aCalendarId)
        );

        $calendarEntry = $calendar->scheduleCalendarEntry(
            $this->calendarIdentityService(),
            $aDescription,
            $aLocation,
            $this->collaboratorService()->ownerFrom($tenant, $anOwnerId),
            new TimeSpan($aTimeSpanBegins, $aTimeSpanEnds),
            new Repetition(RepeatType::valueOf($aRepeatType), $aRepeatEndsOnDate),
            new Alarm(AlarmUnitsType::valueOf($anAlarmType), $anAlarmUnits),
            $this->inviteesFrom($tenant, $aParticipantsToInvite)
        );

        $this->calendarEntryRepository()->save($calendarEntry);

        $aCalendarCommandResult->resultingCalendarId($aCalendarId);
        $aCalendarCommandResult->resultingCalendarEntryId($calendarEntry->calendarEntryId()->id());
    }

    public function shareCalendarWith(
        $aTenantId,
        $aCalendarId,
        Collection $aParticipantsToSharedWith
    ) {

        $tenant = new Tenant($aTenantId);

        $calendar = $this->calendarRepository()->calendarOfId(
            $tenant,
            new CalendarId($aCalendarId)
        );

        $sharers = $this->sharersFrom($tenant, $aParticipantsToSharedWith);

        foreach ($sharers as $sharer) {
            $calendar->shareCalendarWith($sharer);
        }

        $this->calendarRepository()->save($calendar);
    }

    public function unshareCalendarWith(
        $aTenantId,
        $aCalendarId,
        Collection $aParticipantsToUnsharedWith
    ) {

        $tenant = new Tenant($aTenantId);

        $calendar = $this->calendarRepository()->calendarOfId(
            $tenant,
            new CalendarId($aCalendarId)
        );

        foreach ($this->sharersFrom($tenant, $aParticipantsToUnsharedWith) as $sharer) {
            $calendar->unshareCalendarWith($sharer);
        }

        $this->calendarRepository()->save($calendar);
    }

    private function calendarRepository()
    {
        return $this->calendarRepository;
    }

    private function calendarEntryRepository()
    {
        return $this->calendarEntryRepository;
    }

    private function calendarIdentityService()
    {
        return $this->calendarIdentityService;
    }

    private function collaboratorService()
    {
        return $this->collaboratorService;
    }

    private function inviteesFrom(
        Tenant $aTenant,
        Collection $aParticipantsToInvite
    ) {

        $invitees = new ArrayCollection();

        foreach ($aParticipantsToInvite as $participatnId) {
            $participant = $this->collaboratorService()->participantFrom($aTenant, $participatnId);

            $invitees->add($participant);
        }

        return $invitees;
    }

    private function sharersFrom(
        Tenant $aTenant,
        Collection $aParticipantsToSharedWith
    ) {

        $sharers = new ArrayCollection();

        foreach ($aParticipantsToSharedWith as $participatnId) {
            $participant = $this->collaboratorService()->participantFrom($aTenant, $participatnId);

            $sharers->add(new CalendarSharer($participant));
        }

        return $sharers;
    }

    /**
     * @return CalendarRepository
     */
    public function getCalendarRepository()
    {
        return $this->calendarRepository;
    }
}