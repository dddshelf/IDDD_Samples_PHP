<?php

namespace SaasOvation\IdentityAccess\Application\Command;

class ChangePostalAddressCommand
{
    private $tenantId;
    private $username;
    private $addressStreetAddress;
    private $addressCity;
    private $addressStateProvince;
    private $addressPostalCode;
    private $addressCountryCode;

    public function __construct($tenantId, $username, $addressStreetAddress,
            $addressCity, $addressStateProvince,
            $addressPostalCode, $addressCountryCode)
    {

        $this->tenantId = $tenantId;
        $this->username = $username;
        $this->addressStreetAddress = $addressStreetAddress;
        $this->addressCity = $addressCity;
        $this->addressStateProvince = $addressStateProvince;
        $this->addressPostalCode = $addressPostalCode;
        $this->addressCountryCode = $addressCountryCode;
    }

    public function getTenantId()
    {
        return $this->tenantId;
    }

    public function setTenantId($tenantId)
    {
        $this->tenantId = $tenantId;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username)
    {
        $this->username = $username;
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
