<?php

namespace SaasOvation\Collaboration\Port\Adapter\Persistence\View;

use SaasOvation\Collaboration\Domain\Model\Calendar\CalendarCreated;
use SaasOvation\Collaboration\Domain\Model\Calendar\CalendarDescriptionChanged;
use SaasOvation\Collaboration\Domain\Model\Calendar\CalendarId;
use SaasOvation\Collaboration\Domain\Model\Calendar\CalendarRenamed;
use SaasOvation\Collaboration\Domain\Model\Calendar\CalendarShared;
use SaasOvation\Collaboration\Domain\Model\Calendar\CalendarSharer;
use SaasOvation\Collaboration\Domain\Model\Calendar\CalendarUnshared;
use SaasOvation\Collaboration\Domain\Model\Tenant\Tenant;
use SaasOvation\Common\Event\Sourcing\EventDispatcher;
use SaasOvation\Common\Port\Adapter\Persistence\AbstractProjection;

class MySQLCalendarProjection extends AbstractProjection implements EventDispatcher
{
    protected function whenCalendarCreated(CalendarCreated $anEvent)
    {
        // idempotent operation
        if (
            $this->exists(
                'SELECT calendar_id FROM tbl_vw_calendar WHERE tenant_id = ? AND calendar_id = ?',
                $anEvent->tenant()->id(),
                $anEvent->calendarId()->id())
        ) {
            return;
        }

        $statement = $this->connection()->prepare(
            'INSERT INTO tbl_vw_calendar(
                 calendar_id,
                 description,
                 name,
                 owner_email_address,
                 owner_identity,
                 owner_name,
                 tenant_id
             ) VALUES (
                 ?,
                 ?,
                 ?,
                 ?,
                 ?,
                 ?,
                 ?
             )');

        $statement->bindValue(1, $anEvent->calendarId()->id());
        $statement->bindValue(2, $anEvent->description());
        $statement->bindValue(3, $anEvent->name());
        $statement->bindValue(4, $anEvent->owner()->emailAddress());
        $statement->bindValue(5, $anEvent->owner()->identity());
        $statement->bindValue(6, $anEvent->owner()->name());
        $statement->bindValue(7, $anEvent->tenant()->id());

        $this->execute($statement);

        foreach ($anEvent->sharedWith() as $sharer) {
            $this->insertCalendarSharer($anEvent->tenant(), $anEvent->calendarId(), $sharer);
        }
    }

    protected function whenCalendarDescriptionChanged(CalendarDescriptionChanged $anEvent)
    {
        $statement = $this->connection()->prepare(
            'UPDATE tbl_vw_calendar SET description = ? WHERE calendar_id = ?'
        );

        $statement->bindValue(1, $anEvent->description());
        $statement->bindValue(2, $anEvent->calendarId()->id());

        $this->execute($statement);
    }

    protected function whenCalendarRenamed(CalendarRenamed $anEvent)
    {
        $statement = $this->connection()->prepare('update tbl_vw_calendar set name = ? where calendar_id = ?');

        $statement->bindValue(1, $anEvent->name());
        $statement->bindValue(2, $anEvent->calendarId()->id());

        $this->execute($statement);
    }

    protected function whenCalendarRemoved(CalendarRenamed $anEvent)
    {
        $statement = $this->connection()->prepare(
            'UPDATE tbl_vw_calendar SET name = ? WHERE calendar_id = ?'
        );

        $statement->bindValue(1, $anEvent->name());
        $statement->bindValue(2, $anEvent->calendarId()->id());

        $this->execute($statement);
    }

    protected function whenCalendarShared(CalendarShared $anEvent)
    {
        $this->insertCalendarSharer(
            $anEvent->tenant(),
            $anEvent->calendarId(),
            $anEvent->calendarSharer()
        );
    }

    protected function whenCalendarUnshared(CalendarUnshared $anEvent)
    {
        $statement = $this->connection()->prepare(
            'DELETE FROM tbl_vw_calendar_sharer WHERE tenant_id = ? AND calendar_id = ? AND participant_identity = ?'
        );

        $statement->bindValue(1, $anEvent->tenant()->id());
        $statement->bindValue(2, $anEvent->calendarId()->id());
        $statement->bindValue(3, $anEvent->calendarSharer()->participant()->identity());

        $this->execute($statement);
    }

    private function insertCalendarSharer(Tenant $aTenant, CalendarId $aCalendarId, CalendarSharer $aCalendarSharer)
    {
        // idempotent operation
        if (
            $this->exists(
                'SELECT id FROM tbl_vw_calendar_sharer WHERE tenant_id = ? AND calendar_id = ? AND participant_identity = ?',
                $aTenant->id(),
                $aCalendarId->id(),
                $aCalendarSharer->participant()->identity())
        ) {
            return;
        }

        $statement = $this->connection()->prepare(
            'INSERT INTO tbl_vw_calendar_sharer (
                 id,
                 calendar_id,
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
        $statement->bindValue(2, $aCalendarId->id());
        $statement->bindValue(3, $aCalendarSharer->participant()->emailAddress());
        $statement->bindValue(4, $aCalendarSharer->participant()->identity());
        $statement->bindValue(5, $aCalendarSharer->participant()->name());
        $statement->bindValue(6, $aTenant->id());

        $this->execute($statement);
    }

    protected function understoodEventTypes()
    {
        return [
            'SaasOvation\Collaboration\Domain\Model\Calendar\CalendarCreated',
            'SaasOvation\Collaboration\Domain\Model\Calendar\CalendarDescriptionChanged',
            'SaasOvation\Collaboration\Domain\Model\Calendar\CalendarRenamed',
            'SaasOvation\Collaboration\Domain\Model\Calendar\CalendarShared',
            'SaasOvation\Collaboration\Domain\Model\Calendar\CalendarUnshared'
        ];
    }
}
