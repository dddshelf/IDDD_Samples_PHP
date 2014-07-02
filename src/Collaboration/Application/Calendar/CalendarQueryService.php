<?php

namespace SaasOvation\Collaboration\Application\Calendar;

use DateTime;
use NotORM_Row;
use SaasOvation\Collaboration\Application\Calendar\Data\CalendarData;
use SaasOvation\Collaboration\Application\Calendar\Data\CalendarSharerData;
use SaasOvation\Common\Port\Adapter\Persistence\AbstractQueryService;
use SaasOvation\Common\Port\Adapter\Persistence\JoinOn;

class CalendarQueryService extends AbstractQueryService
{
    public function allCalendarsDataOfTenant($aTenantId)
    {
        $calendarDataRows = $this->database()->calendar('tenant_id', $aTenantId);

        $calendarDatas = [];

        foreach ($calendarDataRows as $aCalendarDataRow) {
            $calendarDatas[] = $this->buildCalendarDataRowFrom($aCalendarDataRow);
        }

        return $calendarDatas;
    }

    public function calendarDataOfId($aTenantId, $aCalendarId)
    {
        return $this->buildCalendarDataRowFrom(
            $this->database()->calendar('tenant_id = ? and calendar_id = ?', $aTenantId, $aCalendarId)->fetch()
        );
    }

    private function buildCalendarDataRowFrom($aCalendarDataRow)
    {
        $aCalendarData = new CalendarData();

        $aCalendarData->setCalendarId($aCalendarDataRow['calendar_id']);
        $aCalendarData->setDescription($aCalendarDataRow['description']);
        $aCalendarData->setName($aCalendarDataRow['name']);
        $aCalendarData->setOwnerEmailAddress($aCalendarDataRow['owner_email_address']);
        $aCalendarData->setOwnerIdentity($aCalendarDataRow['owner_identity']);
        $aCalendarData->setOwnerName($aCalendarDataRow['owner_name']);
        $aCalendarData->setTenantId($aCalendarDataRow['tenant_id']);
        $aCalendarData->setSharers($this->fetchCalendarDataSharersFrom($aCalendarDataRow));

        return $aCalendarData;
    }

    private function fetchCalendarDataSharersFrom($aCalendarDataRow)
    {
        $sharers = [];

        foreach ($aCalendarDataRow->calendar_sharer() as $aCalendarSharerDataRow) {
            $aCalendarSharerData = new CalendarSharerData();
            $aCalendarSharerData->setCalendarId($aCalendarSharerDataRow['calendar_id']);
            $aCalendarSharerData->setParticipantEmailAddress($aCalendarSharerDataRow['participant_email_address']);
            $aCalendarSharerData->setParticipantName($aCalendarSharerDataRow['participant_name']);
            $aCalendarSharerData->setParticipantIdentity($aCalendarSharerDataRow['participant_identity']);
            $aCalendarSharerData->setTenantId($aCalendarSharerDataRow['tenant_id']);

            $sharers[] = $aCalendarSharerData;
        }

        return $sharers;
    }
}
