<?php

namespace SaasOvation\IdentityAccess\Application\Command;

class ProvisionTenantCommand
{
    private $tenantName;
    private $tenantDescription;
    private $administorFirstName;
    private $administorLastName;
    private $emailAddress;
    private $primaryTelephone;
    private $secondaryTelephone;
    private $addressStreetAddress;
    private $addressCity;
    private $addressStateProvince;
    private $addressPostalCode;
    private $addressCountryCode;

    public function __construct($tenantName, $tenantDescription, $administorFirstName,
            $administorLastName, $emailAddress, $primaryTelephone, $secondaryTelephone,
            $addressStreetAddress, $addressCity, $addressStateProvince, $addressPostalCode,
            $addressCountryCode
    ) {
        $this->tenantName = $tenantName;
        $this->tenantDescription = $tenantDescription;
        $this->administorFirstName = $administorFirstName;
        $this->administorLastName = $administorLastName;
        $this->emailAddress = $emailAddress;
        $this->primaryTelephone = $primaryTelephone;
        $this->secondaryTelephone = $secondaryTelephone;
        $this->addressStreetAddress = $addressStreetAddress;
        $this->addressCity = $addressCity;
        $this->addressStateProvince = $addressStateProvince;
        $this->addressPostalCode = $addressPostalCode;
        $this->addressCountryCode = $addressCountryCode;
    }

    public function getTenantName()
    {
        return $this->tenantName;
    }

    public function setTenantName($tenantName)
    {
        $this->tenantName = $tenantName;
    }

    public function getTenantDescription()
    {
        return $this->tenantDescription;
    }

    public function setTenantDescription($tenantDescription)
    {
        $this->tenantDescription = $tenantDescription;
    }

    public function getAdministorFirstName()
    {
        return $this->administorFirstName;
    }

    public function setAdministorFirstName($administorFirstName)
    {
        $this->administorFirstName = $administorFirstName;
    }

    public function getAdministorLastName()
    {
        return $this->administorLastName;
    }

    public function setAdministorLastName($administorLastName)
    {
        $this->administorLastName = $administorLastName;
    }

    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    public function setEmailAddress($emailAddress)
    {
        $this->emailAddress = $emailAddress;
    }

    public function getPrimaryTelephone()
    {
        return $this->primaryTelephone;
    }

    public function setPrimaryTelephone($primaryTelephone)
    {
        $this->primaryTelephone = $primaryTelephone;
    }

    public function getSecondaryTelephone()
    {
        return $this->secondaryTelephone;
    }

    public function setSecondaryTelephone($secondaryTelephone)
    {
        $this->secondaryTelephone = $secondaryTelephone;
    }

    public function getAddressStreetAddress()
    {
        return $this->addressStreetAddress;
    }

    public function setAddressStreetAddress($addressStreetAddress)
    {
        $this->addressStreetAddress = $addressStreetAddress;
    }

    public function getAddressCity()
    {
        return $this->addressCity;
    }

    public function setAddressCity($addressCity)
    {
        $this->addressCity = $addressCity;
    }

    public function getAddressStateProvince()
    {
        return $this->addressStateProvince;
    }

    public function setAddressStateProvince($addressStateProvince)
    {
        $this->addressStateProvince = $addressStateProvince;
    }

    public function getAddressPostalCode()
    {
        return $this->addressPostalCode;
    }

    public function setAddressPostalCode($addressPostalCode)
    {
        $this->addressPostalCode = $addressPostalCode;
    }

    public function getAddressCountryCode()
    {
        return $this->addressCountryCode;
    }

    public function setAddressCountryCode($addressCountryCode)
    {
        $this->addressCountryCode = $addressCountryCode;
    }
}
