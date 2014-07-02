<?php

namespace SaasOvation\Collaboration\Test\Application\Calendar;

use DateTimeImmutable;
use SaasOvation\Collaboration\Test\Application\ApplicationTest;
use SaasOvation\Collaboration\Domain\Model\DomainRegistry;

class CalendarEntryQueryServiceTest extends ApplicationTest
{
    public function testCalendarEntryDataOfId()
    {
        $calendarEntry = $this->calendarEntryAggregate();

        DomainRegistry::calendarEntryRepository()->save($calendarEntry);

        $calendarEntryData = $this->calendarEntryQueryService->calendarEntryDataOfId(
            $calendarEntry->tenant()->id(),
            $calendarEntry->calendarEntryId()->id()
        );

        $this->assertNotNull($calendarEntryData);
        $this->assertNotNull($calendarEntryData->getAlarmAlarmUnitsType());
        $this->assertNotNull($calendarEntryData->getCalendarEntryId());
        $this->assertNotNull($calendarEntryData->getCalendarId());
        $this->assertEquals($calendarEntry->calendarId()->id(), $calendarEntryData->getCalendarId());
        $this->assertNotNull($calendarEntryData->getDescription());
        $this->assertNotNull($calendarEntryData->getLocation());
        $this->assertNotNull($calendarEntryData->getOwnerEmailAddress());
        $this->assertNotNull($calendarEntryData->getOwnerIdentity());
        $this->assertNotNull($calendarEntryData->getOwnerName());
        $this->assertNotNull($calendarEntryData->getRepetitionType());
        $this->assertEquals($calendarEntry->tenant()->id(), $calendarEntryData->getTenantId());
        $this->assertNotNull($calendarEntryData->getInvitees());
        $this->assertEmpty($calendarEntryData->getInvitees());
    }

    public function testCalendarEntryDataOfCalendarId()
    {
        $calendarEntries = $this->calendarEntryAggregates();

        foreach ($calendarEntries as $calendarEntry) {
            DomainRegistry::calendarEntryRepository()->save($calendarEntry);
        }

        $queriedCalendarEntries = $this->calendarEntryQueryService->calendarEntryDataOfCalendarId(
            $calendarEntries[0]->tenant()->id(),
            $calendarEntries[0]->calendarId()->id()
        );

        $this->assertNotNull($queriedCalendarEntries);
        $this->assertNotEmpty($queriedCalendarEntries);
        $this->assertCount(count($queriedCalendarEntries), $calendarEntries);

        foreach ($queriedCalendarEntries as $calendarEntryData) {
            $this->assertNotNull($calendarEntryData);
            $this->assertNotNull($calendarEntryData->getAlarmAlarmUnitsType());
            $this->assertNotNull($calendarEntryData->getCalendarEntryId());
            $this->assertNotNull($calendarEntryData->getCalendarId());
            $this->assertEquals($calendarEntries[0]->calendarId()->id(), $calendarEntryData->getCalendarId());
            $this->assertNotNull($calendarEntryData->getDescription());
            $this->assertNotNull($calendarEntryData->getLocation());
            $this->assertNotNull($calendarEntryData->getOwnerEmailAddress());
            $this->assertNotNull($calendarEntryData->getOwnerIdentity());
            $this->assertNotNull($calendarEntryData->getOwnerName());
            $this->assertNotNull($calendarEntryData->getRepetitionType());
            $this->assertEquals($calendarEntries[0]->tenant()->id(), $calendarEntryData->getTenantId());
            $this->assertNotNull($calendarEntryData->getInvitees());
            $this->assertNotEmpty($calendarEntryData->getInvitees());
        }
    }

    public function testTimeSpanningCalendarEntries()
    {
        $calendarEntries = $this->calendarEntryAggregates();

        $this->assertCount(3, $calendarEntries);

        foreach ($calendarEntries as $calendarEntry) {
            DomainRegistry::calendarEntryRepository()->save($calendarEntry);
        }

        $earliestDate = new DateTimeImmutable();
        $latestDate = $earliestDate;

        foreach ($calendarEntries as $calendarEntry) {
            if ($calendarEntry->timeSpan()->begins() < $earliestDate) {
                $earliestDate = $calendarEntry->timeSpan()->begins();
            }

            if ($calendarEntry->timeSpan()->ends() > $latestDate) {
                $latestDate = $calendarEntry->timeSpan()->ends();
            }

            $queriedCalendarEntries = $this->calendarEntryQueryService->timeSpanningCalendarEntries(
                $calendarEntry->tenant()->id(),
                $calendarEntry->calendarId()->id(),
                $this->beginningOfDay($calendarEntry->timeSpan()->begins()),
                $this->endOfDay($calendarEntry->timeSpan()->ends())
            );

            $this->assertNotNull($queriedCalendarEntries);
            $this->assertNotEmpty($queriedCalendarEntries);
            $this->assertCount(1, $queriedCalendarEntries);

            $calendarEntryData = current($queriedCalendarEntries);

            $this->assertNotNull($calendarEntryData->getAlarmAlarmUnitsType());
            $this->assertNotNull($calendarEntryData->getCalendarEntryId());
            $this->assertNotNull($calendarEntryData->getCalendarId());
            $this->assertEquals($calendarEntries[0]->calendarId()->id(), $calendarEntryData->getCalendarId());
            $this->assertNotNull($calendarEntryData->getDescription());
            $this->assertNotNull($calendarEntryData->getLocation());
            $this->assertNotNull($calendarEntryData->getOwnerEmailAddress());
            $this->assertNotNull($calendarEntryData->getOwnerIdentity());
            $this->assertNotNull($calendarEntryData->getOwnerName());
            $this->assertNotNull($calendarEntryData->getRepetitionType());
        }

        $queriedCalendarEntries = $this->calendarEntryQueryService->timeSpanningCalendarEntries(
            $calendarEntries[0]->tenant()->id(),
            $calendarEntries[0]->calendarId()->id(),
            $this->beginningOfDay($earliestDate),
            $this->endOfDay($latestDate)
        );

        $this->assertNotNull($queriedCalendarEntries);
        $this->assertNotEmpty($queriedCalendarEntries);
        $this->assertCount(3, $queriedCalendarEntries);
    }
}
