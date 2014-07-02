<?php

namespace SaasOvation\Collaboration\Domain\Model\Calendar;

use DateTimeInterface;
use SaasOvation\Common\AssertionConcern;

final class TimeSpan extends AssertionConcern
{
    /**
     * @var DateTimeInterface
     */
    private $begins;

    /**
     * @var DateTimeInterface
     */
    private $ends;

    public function __construct(DateTimeInterface $aBegins, DateTimeInterface $anEnds)
    {
        $this->assertCorrectTimeSpan($aBegins, $anEnds);
    
        $this->setBegins($aBegins);
        $this->setEnds($anEnds);
    }

    public function begins()
    {
        return $this->begins;
    }

    public function ends()
    {
        return $this->ends;
    }

    public function equals($anObject)
    {
        $equalObjects = false;

        if (null !== $anObject && get_class($this) === $anObject->getClass()) {
            $equalObjects =
                $this->begins()->equals($anObject->begins()) &&
                $this->ends()->equals($anObject->ends());
        }

        return $equalObjects;
    }

    public function __toString()
    {
        return 'TimeSpan [begins=' . $this->begins . ", ends=" . $this->ends . "]";
    }

    private function assertCorrectTimeSpan(DateTimeInterface $aBegins, DateTimeInterface $anEnds)
    {
        $this->assertArgumentNotNull($aBegins, 'Must provide begins.');
        $this->assertArgumentNotNull($anEnds, 'Must provide ends.');
        $this->assertArgumentFalse($aBegins > $anEnds, 'Time span must not end before it begins.');
    }

    private function setBegins(DateTimeInterface $aBegins)
    {
        $this->begins = $aBegins;
    }

    private function setEnds(DateTimeInterface $anEnds)
    {
        $this->ends = $anEnds;
    }
}
