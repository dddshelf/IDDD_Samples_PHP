<?php

namespace SaasOvation\Collaboration\Application\Calendar;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use SaasOvation\Collaboration\Application\Calendar\Data\CalendarEntryData;
use SaasOvation\Collaboration\Application\Calendar\Data\CalendarEntryInviteeData;
use SaasOvation\Common\Port\Adapter\Persistence\AbstractQueryService;
use SaasOvation\Common\Port\Adapter\Persistence\JoinOn;

class CalendarEntryQueryService extends AbstractQueryService
{
    public function calendarEntryDataOfId($aTenantId, $aCalendarEntryId)
    {
        $aCalendarEntryRow = $this->database()->calendar_entry('tenant_id = ? and calendar_entry_id = ?', $aTenantId, $aCalendarEntryId)->fetch();

        $calendarEntryData = $this->buildCalendarEntryRowFrom($aCalendarEntryRow);

        return $calendarEntryData;
    }

    public function calendarEntryDataOfCalendarId($aTenantId, $aCalendarId)
    {
        $rows = $this->database()->calendar_entry(
            'tenant_id = ? and calendar_id = ?',
            $aTenantId,
            $aCalendarId
        );

        $calendarEntries = [];

        foreach ($rows as $aCalendarEntryRow) {
            $calendarEntries[] = $this->buildCalendarEntryRowFrom($aCalendarEntryRow);
        }

        return $calendarEntries;
    }


    public function timeSpanningCalendarEntries($aTenantId, $aCalendarId, DateTimeInterface $aTimeSpanBegins, DateTimeInterface $aTimeSpanEnds)
    {
        $rows =
            $this->database()->calendar_entry()
            ->where('tenant_id', $aTenantId)
            ->and('calendar_id', $aCalendarId)
            ->and(
                '(time_span_begins between ? and ?) or (repetition_ends between ? and ?)',
                $aTimeSpanBegins->format('Y-m-d H:i:s'),
                $aTimeSpanEnds->format('Y-m-d H:i:s'),
                $aTimeSpanBegins->format('Y-m-d H:i:s'),
                $aTimeSpanEnds->format('Y-m-d H:i:s')
            )
        ;

        $calendarEntries = [];

        foreach ($rows as $aCalendarEntryRow) {
            $calendarEntries[] = $this->buildCalendarEntryRowFrom($aCalendarEntryRow);
        }

        return $calendarEntries;
    }

    private function calendarEntryInviteesFrom($aRow)
    {
        $invitees = [];

        foreach ($aRow->calendar_entry_invitee() as $anInviteeRow) {
            $anInvitee = new CalendarEntryInviteeData();
            $anInvitee->setTenantId($anInviteeRow['tenant_id']);
            $anInvitee->setCalendarEntryId($anInviteeRow['calendar_entry_id']);
            $anInvitee->setParticipantEmailAddress($anInviteeRow['participant_email_address']);
            $anInvitee->setParticipantIdentity($anInviteeRow['participant_identity']);
            $anInvitee->setTenantId($anInviteeRow['tenant_id']);

            $invitees[] = $anInvitee;
        }

        return $invitees;
    }

    private function buildCalendarEntryRowFrom($aCalendarEntryRow)
    {
        $calendarEntryData = new CalendarEntryData();

        $calendarEntryData->setCalendarEntryId($aCalendarEntryRow['calendar_entry_id']);
        $calendarEntryData->setAlarmAlarmUnits($aCalendarEntryRow['alarm_alarm_units']);
        $calendarEntryData->setAlarmAlarmUnitsType($aCalendarEntryRow['alarm_alarm_units_type']);
        $calendarEntryData->setCalendarId($aCalendarEntryRow['calendar_id']);
        $calendarEntryData->setDescription($aCalendarEntryRow['description']);
        $calendarEntryData->setLocation($aCalendarEntryRow['location']);
        $calendarEntryData->setOwnerEmailAddress($aCalendarEntryRow['owner_email_address']);
        $calendarEntryData->setOwnerIdentity($aCalendarEntryRow['owner_identity']);
        $calendarEntryData->setOwnerName($aCalendarEntryRow['owner_name']);
        $calendarEntryData->setRepetitionEnds(DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $aCalendarEntryRow['repetition_ends']));
        $calendarEntryData->setRepetitionType($aCalendarEntryRow['repetition_type']);
        $calendarEntryData->setTenantId($aCalendarEntryRow['tenant_id']);
        $calendarEntryData->setTimeSpanBegins(DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $aCalendarEntryRow['time_span_begins']));
        $calendarEntryData->setTimeSpanEnds(DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $aCalendarEntryRow['time_span_ends']));

        $calendarEntryData->setInvitees(
            $this->calendarEntryInviteesFrom($aCalendarEntryRow)
        );

        return $calendarEntryData;
    }
}
