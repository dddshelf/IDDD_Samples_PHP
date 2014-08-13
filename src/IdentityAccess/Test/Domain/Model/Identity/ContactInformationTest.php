<?php

namespace SaasOvation\IdentityAccess\Test\Domain\Model\Identity;

use SaasOvation\IdentityAccess\Domain\Model\Identity\ContactInformation;
use SaasOvation\IdentityAccess\Domain\Model\Identity\EmailAddress;
use SaasOvation\IdentityAccess\Domain\Model\Identity\PostalAddress;
use SaasOvation\IdentityAccess\Domain\Model\Identity\Telephone;
use SaasOvation\IdentityAccess\Test\Domain\Model\IdentityAccessTest;

class ContactInformationTest extends IdentityAccessTest
{
    public function testContactInformation()
    {
        $contactInformation = $this->contactInformation();

        $this->assertEquals(self::$FIXTURE_USER_EMAIL_ADDRESS, $contactInformation->emailAddress()->address());
        $this->assertEquals('Boulder', $contactInformation->postalAddress()->city());
        $this->assertEquals('CO', $contactInformation->postalAddress()->stateProvince());
    }

    public function testChangeEmailAddress()
    {
        $contactInformation = $this->contactInformation();
        $contactInformationCopy = ContactInformation::fromContactInformation($contactInformation);

        $contactInformation2 = $contactInformation->changeEmailAddress(
            new EmailAddress(self::$FIXTURE_USER_EMAIL_ADDRESS2)
        );

        $this->assertEquals($contactInformationCopy, $contactInformation);
        $this->assertFalse($contactInformation->equals($contactInformation2));
        $this->assertFalse($contactInformationCopy->equals($contactInformation2));

        $this->assertEquals(self::$FIXTURE_USER_EMAIL_ADDRESS, $contactInformation->emailAddress()->address());
        $this->assertEquals(self::$FIXTURE_USER_EMAIL_ADDRESS2, $contactInformation2->emailAddress()->address());
        $this->assertEquals('Boulder', $contactInformation->postalAddress()->city());
        $this->assertEquals('CO', $contactInformation->postalAddress()->stateProvince());
    }

    public function testChangePostalAddress()
    {
        $contactInformation = $this->contactInformation();
        $contactInformationCopy = ContactInformation::fromContactInformation($contactInformation);

        $contactInformation2 = $contactInformation->changePostalAddress(
            new PostalAddress('321 Mockingbird Lane', 'Denver', 'CO', '81121', 'US')
        );

        $this->assertEquals($contactInformationCopy, $contactInformation);
        $this->assertFalse($contactInformation->equals($contactInformation2));
        $this->assertFalse($contactInformationCopy->equals($contactInformation2));

        $this->assertEquals('321 Mockingbird Lane', $contactInformation2->postalAddress()->streetAddress());
        $this->assertEquals('Denver', $contactInformation2->postalAddress()->city());
        $this->assertEquals('CO', $contactInformation2->postalAddress()->stateProvince());
    }

    public function testChangePrimaryTelephone()
    {
        $contactInformation = $this->contactInformation();
        $contactInformationCopy = ContactInformation::fromContactInformation($contactInformation);

        $contactInformation2 = $contactInformation->changePrimaryTelephone(
            new Telephone('720-555-1212')
        );

        $this->assertEquals($contactInformationCopy, $contactInformation);
        $this->assertFalse($contactInformation->equals($contactInformation2));
        $this->assertFalse($contactInformationCopy->equals($contactInformation2));

        $this->assertEquals('720-555-1212', $contactInformation2->primaryTelephone()->number());
        $this->assertEquals('Boulder', $contactInformation2->postalAddress()->city());
        $this->assertEquals('CO', $contactInformation2->postalAddress()->stateProvince());
    }

    public function testChangeSecondaryTelephone()
    {
        $contactInformation = $this->contactInformation();
        $contactInformationCopy = ContactInformation::fromContactInformation($contactInformation);

        $contactInformation2 = $contactInformation->changeSecondaryTelephone(
            new Telephone('720-555-1212')
        );

        $this->assertEquals($contactInformationCopy, $contactInformation);
        $this->assertFalse($contactInformation->equals($contactInformation2));
        $this->assertFalse($contactInformationCopy->equals($contactInformation2));

        $this->assertEquals('720-555-1212', $contactInformation2->secondaryTelephone()->number());
        $this->assertEquals('Boulder', $contactInformation2->postalAddress()->city());
        $this->assertEquals('CO', $contactInformation2->postalAddress()->stateProvince());
    }
}
