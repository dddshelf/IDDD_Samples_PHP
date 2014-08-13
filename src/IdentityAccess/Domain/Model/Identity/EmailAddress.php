<?php

namespace SaasOvation\IdentityAccess\Domain\Model\Identity;

use SaasOvation\Common\AssertionConcern;

final class EmailAddress extends AssertionConcern
{
    /**
     * @var string
     */
    private $address;

    public function __construct($anAddress)
    {
        $this->setAddress($anAddress);
    }

    public static function createFromEmailAddress(EmailAddress $anEmailAddress)
    {
        return new EmailAddress($anEmailAddress->address());
    }

    public function address()
    {
        return $this->address;
    }

    public function equals($anObject)
    {
        $equalObjects = false;

        if (null !== $anObject && get_class($this) === get_class($anObject)) {
            $equalObjects = $this->address() === $anObject->address();
        }

        return $equalObjects;
    }

    public function __toString()
    {
        return 'EmailAddress [address=' . $this->address . "]";
    }

    private function setAddress($anAddress)
    {
        $this->assertArgumentNotEmpty($anAddress, 'The email address is required.');
        $this->assertArgumentLength($anAddress, 1, 100, 'Email address must be 100 characters or less.');
        $this->assertArgumentIsAnEmailAddress($anAddress, sprintf('The email address "%s" is invalid.', $anAddress));

        $this->address = $anAddress;
    }
}
