<?php

namespace SaasOvation\IdentityAccess\Domain\Model\Identity;

use SaasOvation\Common\AssertionConcern;

final class FullName extends AssertionConcern
{
    /**
     * @var string
     */
    private $firstName;

    /**
     * @var string
     */
    private $lastName;

    public function __construct($aFirstName, $aLastName)
    {
        $this->setFirstName($aFirstName);
        $this->setLastName($aLastName);
    }

    public static function createFromFullName(FullName $aFullName)
    {
        return new FullName($aFullName->firstName(), $aFullName->lastName());
    }

    public function asFormattedName()
    {
        return $this->firstName() . ' ' . $this->lastName();
    }

    public function firstName()
    {
        return $this->firstName;
    }

    public function lastName()
    {
        return $this->lastName;
    }

    public function withChangedFirstName($aFirstName)
    {
        return new FullName($aFirstName, $this->lastName());
    }

    public function withChangedLastName($aLastName)
    {
        return new FullName($this->firstName(), $aLastName);
    }

    public function equals($anObject)
    {
        $equalObjects = false;

        if ($anObject !== null && get_class($this) === get_class($anObject)) {
            $equalObjects =
                $this->firstName()->equals($anObject->firstName()) &&
                $this->lastName()->equals($anObject->lastName());
        }

        return $equalObjects;
    }

    public function __toString()
    {
        return 'FullName [firstName=' . $this->firstName . ', lastName=' . $this->lastName . ']';
    }

    private function setFirstName($aFirstName)
    {
        $this->assertArgumentNotEmpty($aFirstName, 'First name is required.');
        $this->assertArgumentLength($aFirstName, 1, 50, 'First name must be 50 characters or less.');
        $this->assertArgumentTrue(
            1 === preg_match('/[A-Z][a-z]*/', $aFirstName),
            'First name must be at least one character in length, starting with a capital letter.'
        );

        $this->firstName = $aFirstName;
    }

    private function setLastName($aLastName)
    {
        $this->assertArgumentNotEmpty($aLastName, 'The last name is required.');
        $this->assertArgumentLength($aLastName, 1, 50, 'The last name must be 50 characters or less.');
        $this->assertArgumentTrue(
            1 === preg_match('/^[a-zA-Z\'][ a-zA-Z\'-]*[a-zA-Z\']?/', $aLastName),
            'Last name must be at least one character in length.'
        );

        $this->lastName = $aLastName;
    }
}
