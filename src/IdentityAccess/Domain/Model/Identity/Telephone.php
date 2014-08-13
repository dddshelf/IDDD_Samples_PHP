<?php

namespace SaasOvation\IdentityAccess\Domain\Model\Identity;

use SaasOvation\Common\AssertionConcern;

final class Telephone extends AssertionConcern
{
    /**
     * @var string
     */
    private $number;

    public function __construct($aNumber)
    {
        $this->setNumber($aNumber);
    }

    public static function createFromTelephone(Telephone $aTelephone)
    {
        return new Telephone($aTelephone->number());
    }

    public function number()
    {
        return $this->number;
    }

    public function equals($anObject)
    {
        $equalObjects = false;

        if (null !== $anObject && get_class($this) === get_class($anObject)) {
            $equalObjects = $this->number() === $anObject->number();
        }

        return $equalObjects;
    }

    public function __toString()
    {
        return 'Telephone [number=' . $this->number . ']';
    }

    private function setNumber($aNumber)
    {
        $this->assertArgumentNotEmpty($aNumber, 'Telephone number is required.');
        $this->assertArgumentLength($aNumber, 5, 20, 'Telephone number may not be more than 20 characters.');
        $this->assertArgumentTrue(
            (1 === preg_match('/((\\(\\d{3}\\))|(\\d{3}-))\\d{3}-\\d{4}/', $aNumber)),
            'Telephone number or its format is invalid.');

        $this->number = $aNumber;
    }
}
