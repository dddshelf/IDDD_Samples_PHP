<?php

namespace SaasOvation\Collaboration\Application\Calendar;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use SaasOvation\Collaboration\Domain\Model\Calendar\Alarm;
use SaasOvation\Collaboration\Domain\Model\Calendar\AlarmUnitsType;
use SaasOvation\Collaboration\Domain\Model\Calendar\CalendarEntryId;
use SaasOvation\Collaboration\Domain\Model\Calendar\CalendarEntryRepository;
use SaasOvation\Collaboration\Domain\Model\Calendar\RepeatType;
use SaasOvation\Collaboration\Domain\Model\Calendar\Repetition;
use SaasOvation\Collaboration\Domain\Model\Calendar\TimeSpan;
use SaasOvation\Collaboration\Domain\Model\Collaborator\CollaboratorService;
use SaasOvation\Collaboration\Domain\Model\Tenant\Tenant;

class CalendarEntryApplicationService
{
    /**
     * @var CalendarEntryRepository
     */
    private $calendarEntryRepository;
    
    /**
     * @var CollaboratorService
     */
    private $collaboratorService;

    public function __construct(
        CalendarEntryRepository $aCalendarEntryRepository,
        CollaboratorService $aCollaboratorService
    ) {
        $this->calendarEntryRepository = $aCalendarEntryRepository;
        $this->collaboratorService = $aCollaboratorService;
    }

    public function changeCalendarEntryDescription(
        $aTenantId,
        $aCalendarEntryId,
        $aDescription
    ) {

        $tenant = new Tenant($aTenantId);

        $calendarEntry = $this->calendarEntryRepository()->calendarEntryOfId(
            $tenant,
            new CalendarEntryId($aCalendarEntryId)
        );

        $calendarEntry->changeDescription($aDescription);

        $this->calendarEntryRepository()->save($calendarEntry);
    }

    public function inviteCalendarEntryParticipant(
        $aTenantId,
        $aCalendarEntryId,
        Collection $aParticipantsToInvite
    ) {

        $tenant = new Tenant($aTenantId);

        $calendarEntry = $this->calendarEntryRepository()->calendarEntryOfId(
            $tenant,
            new CalendarEntryId($aCalendarEntryId)
        );

        foreach ($this->inviteesFrom($tenant, $aParticipantsToInvite) as $participant) {
            $calendarEntry->invite($participant);
        }

        $this->calendarEntryRepository()->save($calendarEntry);
    }

    public function relocateCalendarEntry(
        $aTenantId,
        $aCalendarEntryId,
        $aLocation
    ) {

        $tenant = new Tenant($aTenantId);

        $calendarEntry = $this->calendarEntryRepository()->calendarEntryOfId(
            $tenant,
            new CalendarEntryId($aCalendarEntryId)
        );

        $calendarEntry->relocate($aLocation);

        $this->calendarEntryRepository()->save($calendarEntry);
    }

    public function rescheduleCalendarEntry(
        $aTenantId,
        $aCalendarEntryId,
        $aDescription,
        $aLocation,
        DateTimeInterface $aTimeSpanBegins,
        DateTimeInterface $aTimeSpanEnds,
        $aRepeatType,
        DateTimeInterface $aRepeatEndsOnDate,
        $anAlarmType,
        $anAlarmUnits
    ) {

        $tenant = new Tenant($aTenantId);

        $calendarEntry = $this->calendarEntryRepository()->calendarEntryOfId(
            $tenant,
            new CalendarEntryId($aCalendarEntryId)
        );

        $calendarEntry->reschedule(
            $aDescription,
            $aLocation,
            new TimeSpan($aTimeSpanBegins, $aTimeSpanEnds),
            new Repetition(RepeatType::valueOf($aRepeatType), $aRepeatEndsOnDate),
            new Alarm(AlarmUnitsType::valueOf($anAlarmType), $anAlarmUnits)
        );

        $this->calendarEntryRepository()->save($calendarEntry);
    }

    public function uninviteCalendarEntryParticipant(
        $aTenantId,
        $aCalendarEntryId,
        Collection $aParticipantsToInvite
    ) {

        $tenant = new Tenant($aTenantId);

        $calendarEntry =
            $this
                ->calendarEntryRepository()
                ->calendarEntryOfId(
                    $tenant,
                    new CalendarEntryId($aCalendarEntryId)
                )
        ;

        foreach ($this->inviteesFrom($tenant, $aParticipantsToInvite) as $participant) {
            $calendarEntry->uninvite($participant);
        }

        $this->calendarEntryRepository()->save($calendarEntry);
    }

    /**
     * @return CalendarEntryRepository
     */
    private function calendarEntryRepository()
    {
        return $this->calendarEntryRepository;
    }

    /**
     * @return CollaboratorService
     */
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
}
