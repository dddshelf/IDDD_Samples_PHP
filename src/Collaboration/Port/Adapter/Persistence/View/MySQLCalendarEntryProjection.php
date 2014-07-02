<?php

namespace SaasOvation\Collaboration\Port\Adapter\Persistence\View;

use PDO;
use PDOStatement;

use SaasOvation\Collaboration\Domain\Model\Calendar\CalendarEntryDescriptionChanged;
use SaasOvation\Collaboration\Domain\Model\Calendar\CalendarEntryId;
use SaasOvation\Collaboration\Domain\Model\Calendar\CalendarEntryParticipantInvited;
use SaasOvation\Collaboration\Domain\Model\Calendar\CalendarEntryParticipantUninvited;
use SaasOvation\Collaboration\Domain\Model\Calendar\CalendarEntryRelocated;
use SaasOvation\Collaboration\Domain\Model\Calendar\CalendarEntryRescheduled;
use SaasOvation\Collaboration\Domain\Model\Calendar\CalendarEntryScheduled;
use SaasOvation\Collaboration\Domain\Model\Collaborator\Participant;
use SaasOvation\Collaboration\Domain\Model\Tenant\Tenant;
use SaasOvation\Common\Event\Sourcing\DispatchableDomainEvent;
use SaasOvation\Common\Event\Sourcing\EventDispatcher;
use SaasOvation\Common\Port\Adapter\Persistence\AbstractProjection;
use Symfony\Component\DependencyInjection\Exception\BadMethodCallException;

class MySQLCalendarEntryProjection extends AbstractProjection implements EventDispatcher
{
    protected function whenCalendarEntryDescriptionChanged(CalendarEntryDescriptionChanged $anEvent)
    {
        $connection = $this->connection();

        $statement = $connection->prepare(
            'UPDATE tbl_vw_calendar_entry SET description = ? WHERE calendar_entry_id = ?'
        );

        $statement->bindValue(1, $anEvent->description());
        $statement->bindValue(2, $anEvent->calendarEntryId()->id());

        $this->execute($statement);
    }

    protected function whenCalendarEntryParticipantInvited(CalendarEntryParticipantInvited $anEvent)
    {
        $this->insertInvitee(
            $anEvent->tenant(),
            $anEvent->calendarEntryId(),
            $anEvent->participant()
        );
    }

    protected function whenCalendarEntryParticipantUninvited(CalendarEntryParticipantUninvited $anEvent)
    {
        $statement = $this->connection()->prepare(
            'DELETE FROM tbl_vw_calendar_entry_invitee WHERE tenant_id = ? AND calendar_entry_id = ? AND participant_identity = ?'
        );

        $statement->bindValue(1, $anEvent->tenant()->id());
        $statement->bindValue(2, $anEvent->calendarEntryId()->id());
        $statement->bindValue(3, $anEvent->participant()->identity());

        $this->execute($statement);
    }

    protected function whenCalendarEntryRelocated(CalendarEntryRelocated $anEvent)
    {
        $statement = $this->connection()->prepare(
            'UPDATE tbl_vw_calendar_entry SET location = ? WHERE calendar_entry_id = ?'
        );

        $statement->bindValue(1, $anEvent->location());
        $statement->bindValue(2, $anEvent->calendarEntryId()->id());

        $this->execute($statement);
    }

    protected function whenCalendarEntryRescheduled(CalendarEntryRescheduled $anEvent)
    {
        $statement = $this->connection()->prepare(
            'UPDATE tbl_vw_calendar_entry
               SET alarm_alarm_units = ?,
                   alarm_alarm_units_type = ?,
                   repetition_ends = ?,
                   repetition_type = ?,
                   time_span_begins = ?,
                   time_span_ends = ?
             WHERE tenant_id = ?
               AND calendar_entry_id = ?'
        );

        $statement->bindValue(1, $anEvent->alarm()->alarmUnits());
        $statement->bindValue(2, $anEvent->alarm()->alarmUnitsType()->name());
        $statement->bindValue(3, $anEvent->repetition()->ends()->getTimestamp());
        $statement->bindValue(4, $anEvent->repetition()->repeats()->name());
        $statement->bindValue(5, $anEvent->timeSpan()->begins()->getTimestamp());
        $statement->bindValue(6, $anEvent->timeSpan()->ends()->getTimestamp());
        $statement->bindValue(7, $anEvent->tenant()->id());
        $statement->bindValue(8, $anEvent->calendarEntryId()->id());

        $this->execute($statement);
    }

    protected function whenCalendarEntryScheduled(CalendarEntryScheduled $anEvent)
    {
        // idempotent operation
        if (
            $this->exists(
                'select calendar_entry_id from tbl_vw_calendar_entry where tenant_id = ? and calendar_entry_id = ?',
                $anEvent->tenant()->id(),
                $anEvent->calendarEntryId()->id()
            )
        ) {
            return;
        }

        $statement = $this->connection()->prepare(
            'INSERT INTO tbl_vw_calendar_entry (
                 calendar_entry_id,
                 alarm_alarm_units,
                 alarm_alarm_units_type,
                 calendar_id, description,
                 location,
                 owner_email_address,
                 owner_identity,
                 owner_name,
                 repetition_ends,
                 repetition_type,
                 tenant_id,
                 time_span_begins,
                 time_span_ends
             ) values(
                 ?,
                 ?,
                 ?,
                 ?,
                 ?,
                 ?,
                 ?,
                 ?,
                 ?,
                 ?,
                 ?,
                 ?,
                 ?,
                 ?
             )'
        );

        $statement->bindValue(1,  $anEvent->calendarEntryId()->id());
        $statement->bindValue(2,  $anEvent->alarm()->alarmUnits());
        $statement->bindValue(3,  $anEvent->alarm()->alarmUnitsType()->name());
        $statement->bindValue(4,  $anEvent->calendarId()->id());
        $statement->bindValue(5,  $anEvent->description());
        $statement->bindValue(6,  $anEvent->location());
        $statement->bindValue(7,  $anEvent->owner()->emailAddress());
        $statement->bindValue(8,  $anEvent->owner()->identity());
        $statement->bindValue(9,  $anEvent->owner()->name());
        $statement->bindValue(10, $anEvent->repetition()->ends()->format('Y-m-d H:i:s'));
        $statement->bindValue(11, $anEvent->repetition()->repeats()->name());
        $statement->bindValue(12, $anEvent->tenant()->id());
        $statement->bindValue(13, $anEvent->timeSpan()->begins()->format('Y-m-d H:i:s'));
        $statement->bindValue(14, $anEvent->timeSpan()->ends()->format('Y-m-d H:i:s'));

        $this->execute($statement);

        foreach ($anEvent->invitees() as $participant) {
            $this->insertInvitee(
                $anEvent->tenant(),
                $anEvent->calendarEntryId(),
                $participant
            );
        }
    }

    private function insertInvitee(Tenant $aTenant, CalendarEntryId $aCalendarEntryId, Participant $aParticipant)
    {
        // idempotent operation
        if (
            $this->exists(
                'SELECT id FROM tbl_vw_calendar_entry_invitee WHERE tenant_id = ? AND calendar_entry_id = ? AND participant_identity = ?',
                $aTenant->id(),
                $aCalendarEntryId->id(),
                $aParticipant->identity())
        ) {
            return;
        }

        $statement = $this->connection()->prepare(
            'INSERT INTO tbl_vw_calendar_entry_invitee (
                 id,
                 calendar_entry_id,
                 participant_email_address,
                 participant_identity,
                 participant_name,
                 tenant_id
             ) values (
                 ?,
                 ?,
                 ?,
                 ?,
                 ?,
                 ?
             )'
        );

        $statement->bindValue(1, 0);
        $statement->bindValue(2, $aCalendarEntryId->id());
        $statement->bindValue(3, $aParticipant->emailAddress());
        $statement->bindValue(4, $aParticipant->identity());
        $statement->bindValue(5, $aParticipant->name());
        $statement->bindValue(6, $aTenant->id());

        $this->execute($statement);
    }

    protected function understoodEventTypes()
    {
        return [
            'SaasOvation\Collaboration\Domain\Model\Calendar\CalendarEntryDescriptionChanged',
            'SaasOvation\Collaboration\Domain\Model\Calendar\CalendarEntryParticipantInvited',
            'SaasOvation\Collaboration\Domain\Model\Calendar\CalendarEntryParticipantUninvited',
            'SaasOvation\Collaboration\Domain\Model\Calendar\CalendarEntryRelocated',
            'SaasOvation\Collaboration\Domain\Model\Calendar\CalendarEntryRescheduled',
            'SaasOvation\Collaboration\Domain\Model\Calendar\CalendarEntryScheduled'
        ];
    }
}
