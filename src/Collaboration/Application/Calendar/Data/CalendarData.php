<?php

namespace SaasOvation\Collaboration\Application\Calendar\Data;

class CalendarData
{
    /**
     * @var string
     */
    private $calendarId;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $ownerEmailAddress;

    /**
     * @var string
     */
    private $ownerIdentity;

    /**
     * @var string
     */
    private $ownerName;

    /**
     * @var CalendarSharerData[]
     */
    private $sharers;

    /**
     * @var string
     */
    private $tenantId;

    public function __construct()
    {
        $this->setSharers([]);
    }

    public function getCalendarId()
    {
        return $this->calendarId;
    }

    public function setCalendarId($calendarId)
    {
        $this->calendarId = $calendarId;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getOwnerEmailAddress()
    {
        return $this->ownerEmailAddress;
    }

    public function setOwnerEmailAddress($ownerEmailAddress)
    {
        $this->ownerEmailAddress = $ownerEmailAddress;
    }

    public function getOwnerIdentity()
    {
        return $this->ownerIdentity;
    }

    public function setOwnerIdentity($ownerIdentity)
    {
        $this->ownerIdentity = $ownerIdentity;
    }

    public function getOwnerName()
    {
        return $this->ownerName;
    }

    public function setOwnerName($ownerName)
    {
        $this->ownerName = $ownerName;
    }

    public function getSharers()
    {
        return $this->sharers;
    }

    public function setSharers(array $sharers)
    {
        $this->sharers = $sharers;
    }

    public function getTenantId()
    {
        return $this->tenantId;
    }

    public function setTenantId($tenantId)
    {
        $this->tenantId = $tenantId;
    }
}
