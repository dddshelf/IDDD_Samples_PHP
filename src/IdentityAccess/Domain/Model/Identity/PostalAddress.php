<?php

namespace SaasOvation\IdentityAccess\Domain\Model\Identity;

use SaasOvation\Common\AssertionConcern;

class PostalAddress extends AssertionConcern
{
    /**
     * @var string
     */
    private $city;

    /**
     * @var string
     */
    private $countryCode;

    /**
     * @var string
     */
    private $postalCode;

    /**
     * @var string
     */
    private $stateProvince;

    /**
     * @var string
     */
    private $streetAddress;

    public function __construct(
        $aStreetAddress,
        $aCity,
        $aStateProvince,
        $aPostalCode,
        $aCountryCode
    ) {
        $this->setCity($aCity);
        $this->setCountryCode($aCountryCode);
        $this->setPostalCode($aPostalCode);
        $this->setStateProvince($aStateProvince);
        $this->setStreetAddress($aStreetAddress);
    }

    public static function createFromPostalAddress(PostalAddress $aPostalAddress)
    {
        return new PostalAddress(
            $aPostalAddress->streetAddress(),
            $aPostalAddress->city(),
            $aPostalAddress->stateProvince(),
            $aPostalAddress->postalCode(),
            $aPostalAddress->countryCode()
        );
    }

    public function city()
    {
        return $this->city;
    }

    public function countryCode()
    {
        return $this->countryCode;
    }

    public function postalCode()
    {
        return $this->postalCode;
    }

    public function stateProvince()
    {
        return $this->stateProvince;
    }

    public function streetAddress()
    {
        return $this->streetAddress;
    }

    public function equals($anObject)
    {
        $equalObjects = false;

        if (null !== $anObject && get_class($this) === get_class($anObject)) {
            $equalObjects =
                $this->streetAddress() === $anObject->streetAddress() &&
                $this->city() === $anObject->city() &&
                $this->stateProvince() === $anObject->stateProvince() &&
                $this->postalCode() === $anObject->postalCode() &&
                $this->countryCode() === $anObject->countryCode();
        }

        return $equalObjects;
    }

    public function __toString()
    {
        return 'PostalAddress [streetAddress=' . $this->streetAddress . ', city=' . $this->city . ', stateProvince=' . $this->stateProvince . ', postalCode=' . $this->postalCode . ', countryCode=' . $this->countryCode . ']';
    }

    private function setCity($aCity)
    {
        $this->assertArgumentNotEmpty($aCity, 'The city is required.');
        $this->assertArgumentLength($aCity, 1, 100, 'The city must be 100 characters or less.');
    
        $this->city = $aCity;
    }

    private function setCountryCode($aCountryCode)
    {
        $this->assertArgumentNotEmpty($aCountryCode, 'The country is required.');
        $this->assertArgumentLength($aCountryCode, 2, 2, 'The country code must be two characters.');
    
        $this->countryCode = $aCountryCode;
    }

    private function setPostalCode($aPostalCode)
    {
        $this->assertArgumentNotEmpty($aPostalCode, 'The postal code is required.');
        $this->assertArgumentLength($aPostalCode, 5, 12, 'The postal code must be 12 characters or less.');
    
        $this->postalCode = $aPostalCode;
    }

    private function setStateProvince($aStateProvince)
    {
        $this->assertArgumentNotEmpty($aStateProvince, 'The state/province is required.');
        $this->assertArgumentLength($aStateProvince, 2, 100, 'The state/province must be 100 characters or less.');
    
        $this->stateProvince = $aStateProvince;
    }

    private function setStreetAddress($aStreetAddress)
    {
        $this->assertArgumentNotEmpty($aStreetAddress, 'The street address is required.');
        $this->assertArgumentLength($aStreetAddress, 1, 100, 'The street address must be 100 characters or less.');
    
        $this->streetAddress = $aStreetAddress;
    }
}
