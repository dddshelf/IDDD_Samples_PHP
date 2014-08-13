<?php

namespace SaasOvation\IdentityAccess\Domain\Model\Identity;

use SaasOvation\Common\AssertionConcern;

final class ContactInformation extends AssertionConcern
{
    /**
     * @var EmailAddress
     */
    private $emailAddress;

    /**
     * @var string
     */
    private $postalAddressCity;

    /**
     * @var string
     */
    private $postalAddressCountryCode;

    /**
     * @var string
     */
    private $postalAddressPostalCode;

    /**
     * @var string
     */
    private $postalAddressStateProvince;

    /**
     * @var string
     */
    private $postalAddressStreetAddress;

    /**
     * @var Telephone
     */
    private $primaryTelephone;

    /**
     * @var Telephone
     */
    private $secondaryTelephone;

    public function __construct(
        EmailAddress $anEmailAddress,
        PostalAddress $aPostalAddress,
        Telephone $aPrimaryTelephone,
        Telephone $aSecondaryTelephone
    ) {
        $this->setEmailAddress($anEmailAddress);
        $this->setPostalAddress($aPostalAddress);
        $this->setPrimaryTelephone($aPrimaryTelephone);
        $this->setSecondaryTelephone($aSecondaryTelephone);
    }

    public static function fromContactInformation(ContactInformation $aContactInformation)
    {
        return new ContactInformation(
            $aContactInformation->emailAddress(),
            $aContactInformation->postalAddress(),
            $aContactInformation->primaryTelephone(),
            $aContactInformation->secondaryTelephone()
        );
    }

    public function changeEmailAddress(EmailAddress $anEmailAddress)
    {
        return new ContactInformation(
            $anEmailAddress,
            $this->postalAddress(),
            $this->primaryTelephone(),
            $this->secondaryTelephone()
        );
    }

    public function changePostalAddress(PostalAddress $aPostalAddress)
    {
        return new ContactInformation(
            $this->emailAddress(),
            $aPostalAddress,
            $this->primaryTelephone(),
            $this->secondaryTelephone()
        );
    }

    public function changePrimaryTelephone(Telephone $aTelephone)
    {
        return new ContactInformation(
            $this->emailAddress(),
            $this->postalAddress(),
            $aTelephone,
            $this->secondaryTelephone()
        );
    }

    public function changeSecondaryTelephone(Telephone $aTelephone)
    {
        return new ContactInformation(
            $this->emailAddress(),
            $this->postalAddress(),
            $this->primaryTelephone(),
            $aTelephone
        );
    }

    public function emailAddress()
    {
        return new EmailAddress($this->emailAddress);
    }

    public function postalAddress()
    {
        return new PostalAddress(
            $this->postalAddressStreetAddress,
            $this->postalAddressCity,
            $this->postalAddressStateProvince,
            $this->postalAddressPostalCode,
            $this->postalAddressCountryCode
        );
    }

    public function primaryTelephone()
    {
        return new Telephone($this->primaryTelephone);
    }

    public function secondaryTelephone()
    {
        return new Telephone($this->secondaryTelephone);
    }

    public function equals($anObject)
    {
        $equalObjects = false;

        if (null !== $anObject && get_class($this) == get_class($anObject)) {
            $equalObjects =
                $this->emailAddress()->equals($anObject->emailAddress()) &&
                $this->postalAddress()->equals($anObject->postalAddress()) &&
                $this->primaryTelephone()->equals($anObject->primaryTelephone()) &&
                (($this->secondaryTelephone() == null && $anObject->secondaryTelephone() == null) ||
                    ($this->secondaryTelephone() != null && $this->secondaryTelephone()->equals($anObject->secondaryTelephone())));
        }

        return $equalObjects;
    }

    public function __toString()
    {
        return 'ContactInformation [emailAddress=' . $this->emailAddress . ', postalAddress=' . $this->postalAddress() . ', primaryTelephone=' . $this->primaryTelephone . ', secondaryTelephone=' . $this->secondaryTelephone . ']';
    }

    private function setEmailAddress(EmailAddress $anEmailAddress)
    {
        $this->assertArgumentNotNull($anEmailAddress, 'The email address is required.');

        $this->emailAddress = $anEmailAddress->address();
    }

    private function setPostalAddress(PostalAddress $aPostalAddress)
    {
        $this->assertArgumentNotNull($aPostalAddress, 'The postal address is required.');

        $this->postalAddressCity = $aPostalAddress->city();
        $this->postalAddressCountryCode = $aPostalAddress->countryCode();
        $this->postalAddressPostalCode = $aPostalAddress->postalCode();
        $this->postalAddressStateProvince = $aPostalAddress->stateProvince();
        $this->postalAddressStreetAddress = $aPostalAddress->streetAddress();
    }

    private function setPrimaryTelephone(Telephone $aPrimaryTelephone)
    {
        $this->assertArgumentNotNull($aPrimaryTelephone, 'The primary telephone is required.');

        $this->primaryTelephone = $aPrimaryTelephone->number();
    }

    private function setSecondaryTelephone(Telephone $aSecondaryTelephone)
    {
        $this->secondaryTelephone = $aSecondaryTelephone->number();
    }
}
