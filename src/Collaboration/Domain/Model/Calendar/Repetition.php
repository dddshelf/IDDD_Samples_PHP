<?php

namespace SaasOvation\Collaboration\Domain\Model\Calendar;

use DateTimeImmutable;
use DateTimeInterface;
use SaasOvation\Collaboration\Domain\Model\Calendar\RepeatType\DoesNotRepeat;
use SaasOvation\Collaboration\Domain\Model\Calendar\RepeatType;
use SaasOvation\Common\AssertionConcern;

final class Repetition extends AssertionConcern
{
    /**
     * @var DateTimeInterface
     */
    private $ends;

    /**
     * @var RepeatType
     */
    private $repeats;

    public static function doesNotRepeatInstance(DateTimeInterface $anEnds)
    {
        return new Repetition(new DoesNotRepeat(), $anEnds);
    }

    public static function indefinitelyRepeatsInstance(RepeatType $aRepeatType)
    {
        $ends = (new DateTimeImmutable())->modify('+1000 year'); // 1000 years from 1/1/1970

        return new Repetition($aRepeatType, $ends);
    }

    public function __construct(RepeatType $aRepeats, DateTimeInterface $anEndsOn)
    {
        $this->setEnds($anEndsOn);
        $this->setRepeats($aRepeats);
    }

    public function ends()
    {
        return $this->ends;
    }

    public function repeats()
    {
        return $this->repeats;
    }

    public function equals($anObject)
    {
        $equalObjects = false;

        if (null !== $anObject && get_class($this) == get_class($anObject)) {
            $equalObjects =
                $this->repeats()->name()->equals($anObject->repeats()->name()) &&
                $this->ends()->equals($anObject->ends());
        }

        return $equalObjects;
    }

    public function __toString()
    {
        return 'Repetition [repeats=' . $this->repeats . ', ends=' . $this->ends . ']';
    }

    private function setEnds(DateTimeInterface $anEnds)
    {
        $this->assertArgumentNotNull($anEnds, 'The ends date must be provided.');

        $this->ends = $anEnds;
    }

    private function setRepeats(RepeatType $aRepeatType)
    {
        $this->assertArgumentNotNull($aRepeatType, 'The repeat type must be provided.');

        $this->repeats = $aRepeatType;
    }
}
