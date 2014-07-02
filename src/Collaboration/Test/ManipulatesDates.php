<?php

namespace SaasOvation\Collaboration\Test;

use Carbon\Carbon;
use DateTimeImmutable;
use DateTimeInterface;
use SaasOvation\Collaboration\Domain\Model\Calendar\Alarm;
use SaasOvation\Collaboration\Domain\Model\Calendar\Repetition;
use SaasOvation\Collaboration\Domain\Model\Calendar\RepeatType;
use SaasOvation\Collaboration\Domain\Model\Calendar\AlarmUnitsType;
use SaasOvation\Collaboration\Domain\Model\Calendar\TimeSpan;

trait ManipulatesDates
{
    protected function weeklyRepetition() {

        return new Repetition(
            new RepeatType\Weekly(),
            $this->tomorrowThroughOneYearLaterTimeSpan()->ends()
        );
    }

    protected function oneHourBeforeAlarm()
    {
        return new Alarm(new AlarmUnitsType\Hours(), 1);
    }

    protected function beginningOfDay(DateTimeInterface $aDate)
    {
        $aNewDate = clone $aDate;

        return $aNewDate->setTime(0, 0, 0);
    }

    protected function endOfDay(DateTimeInterface $aDate)
    {
        $aNewDate = clone $aDate;

        return $aNewDate->setTime(23, 59, 59);
    }

    protected function daysFromNowOneHourTimeSpan($aNumberOfDays)
    {
        $aBegins = (new DateTimeImmutable())->modify(sprintf('+%d day', $aNumberOfDays))->setTime(0, 0, 0);

        $anEnds = clone $aBegins;
        $anEnds = $anEnds->modify('+1 hour');

        if ((int) $aBegins->format('H') > (int) $anEnds->format('H')) {
            $anEnds->modify('+1 day');
        }

        return new TimeSpan(
            $aBegins,
            $anEnds
        );
    }

    protected function oneWeekAroundTimeSpan()
    {
        $date1 = new DateTimeImmutable();
        $idx = 0;
        for ( ; $idx < 3; ++$idx) {
            if (1 == $date1->format('d')) {
                break;
            }

            $date1 = $date1->modify('-1 day');
        }

        $date1 = $date1->setTime(0, 0, 0);

        $date2          = new DateTimeImmutable();
        $currentDate    = (int) $date2->format('d');
        $currentMonth   = (int) $date2->format('M');
        $total          = 7 - $idx - 1;

        for ($idx = 0; $idx < $total; ++$idx) {
            $date2 = $date2->modify('+1 day');

            if ($currentDate === (int) $date2->format('d')) {
                $date2 = $date2->modify('+1 month');
                if ($currentMonth === (int) $date2->format('M')) {
                    $date2 = $date2->modify('+1 year');
                }
            }
        }

        $date2->setTime(0, 0, 0);

        return new TimeSpan(
            $date1, $date2
        );
    }

    protected function oneDayPriorTimeSpan()
    {
        $date1 = new DateTimeImmutable();
        $date1 = $date1->modify('-1 day');
        $date1 = $date1->setTime(0, 0, 0);

        $date2 = new DateTimeImmutable();
        $date2 = $date2->setTime(23, 59, 59);

        return new TimeSpan(
            $date1,
            $date2
        );
    }

    protected function tomorrowOneHourTimeSpan()
    {
        $date1 = (new DateTimeImmutable())->modify('+1 day')->setTime(0, 0, 0);
        $date2 = clone $date1;
        $date2 = $date2->modify('+1 hour');

        if ((int) $date1->format('H') > (int) $date2->format('H')) {
            $date2 = $date2->modify('+1 day');
        }

        return new TimeSpan(
            $date1,
            $date2
        );
    }

    protected function tomorrowThroughOneYearLaterTimeSpan()
    {
        $date1 = new DateTimeImmutable();
        $date1->modify('+1 day');
        $date1->setTime(0, 0, 0);

        $date2 = clone $date1;
        $date2 = $date2->modify('+1 year');

        return new TimeSpan(
            $date1,
            $date2
        );
    }
}
