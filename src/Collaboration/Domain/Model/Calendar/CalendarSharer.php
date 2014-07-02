<?php

namespace SaasOvation\Collaboration\Domain\Model\Calendar;

use SaasOvation\Collaboration\Domain\Model\Collaborator\Participant;
use SaasOvation\Common\AssertionConcern;
use SaasOvation\Common\Domain\Model\Comparable;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

final class CalendarSharer
    extends AssertionConcern
    implements Comparable
{
    /**
     * @var Participant
     */
    private $participant;

    public function __construct(Participant $aParticipant)
    {
        $this->setParticipant($aParticipant);
    }

    public function participant()
    {
        return $this->participant;
    }

    public function compareTo($aCalendarSharer)
    {
        if (!$aCalendarSharer instanceof CalendarSharer) {
            throw new InvalidArgumentException('Expecting an instance of CalendarSharer');
        }

        return $this->participant()->compareTo($aCalendarSharer->participant());
    }

    public function equals($anObject)
    {
        $equalObjects = false;

        if (null !== $anObject && get_class($this) == get_class($anObject)) {
            $equalObjects = $this->participant()->equals($anObject->participant());
        }

        return $equalObjects;
    }

    public function toString()
    {
        return 'CalendarSharer [participant=' . $this->participant . "]";
    }

    private function setParticipant(Participant $aParticipant)
    {
        $this->assertArgumentNotNull($aParticipant, 'Participant must be provided.');

        $this->participant = $aParticipant;
    }
}
