<?php

namespace SaasOvation\IdentityAccess\Application\Command;

use DateTimeInterface;

class DefineUserEnablementCommand
{
    private $tenantId;
    private $username;
    private $enabled;
    private $startDate;
    private $endDate;

    public function __construct($tenantId, $username, $enabled, DateTimeInterface $startDate, DateTimeInterface $endDate)
    {
        $this->tenantId = $tenantId;
        $this->username = $username;
        $this->enabled = $enabled;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
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

    public function isEnabled()
    {
        return $this->enabled;
    }

    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    public function getStartDate()
    {
        return $this->startDate;
    }

    public function setStartDate(DateTimeInterface $startDate)
    {
        $this->startDate = $startDate;
    }

    public function getEndDate()
    {
        return $this->endDate;
    }

    public function setEndDate(DateTimeInterface $endDate)
    {
        $this->endDate = $endDate;
    }
}
