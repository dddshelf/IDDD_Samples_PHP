<?php

namespace SaasOvation\Collaboration\Application\Calendar\Data;

use DateTimeInterface;

class CalendarEntryData
{
    /**
     * @var int
     */
    private $alarmAlarmUnits;

    /**
     * @var string
     */
    private $alarmAlarmUnitsType;

    /**
     * @var string
     */
    private $calendarEntryId;

    /**
     * @var string
     */
    private $calendarId;

    /**
     * @var string
     */
    private $description;

    /**
     * @var CalendarEntryInviteeData[]
     */
    private $invitees = [];

    /**
     * @var string
     */
    private $location;

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
     * @var DateTimeInterface
     */
    private $repetitionEnds;

    /**
     * @var string
     */
    private $repetitionType;

    /**
     * @var string
     */
    private $tenantId;

    /**
     * @var DateTimeInterface
     */
    private $timeSpanBegins;

    /**
     * @var DateTimeInterface
     */
    private $timeSpanEnds;

    /**
     * @return int
     */
    public function getAlarmAlarmUnits()
    {
        return $this->alarmAlarmUnits;
    }

    /**
     * @param int $alarmAlarmUnits
     */
    public function setAlarmAlarmUnits($alarmAlarmUnits)
    {
        $this->alarmAlarmUnits = $alarmAlarmUnits;
    }

    /**
     * @return string
     */
    public function getAlarmAlarmUnitsType()
    {
        return $this->alarmAlarmUnitsType;
    }

    /**
     * @param string $alarmAlarmUnitsType
     */
    public function setAlarmAlarmUnitsType($alarmAlarmUnitsType)
    {
        $this->alarmAlarmUnitsType = $alarmAlarmUnitsType;
    }

    /**
     * @return string
     */
    public function getCalendarEntryId()
    {
        return $this->calendarEntryId;
    }

    /**
     * @param string $calendarEntryId
     */
    public function setCalendarEntryId($calendarEntryId)
    {
        $this->calendarEntryId = $calendarEntryId;
    }

    /**
     * @return string
     */
    public function getCalendarId()
    {
        return $this->calendarId;
    }

    /**
     * @param string $calendarId
     */
    public function setCalendarId($calendarId)
    {
        $this->calendarId = $calendarId;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return CalendarEntryInviteeData[]
     */
    public function getInvitees()
    {
        return $this->invitees;
    }

    /**
     * @param CalendarEntryInviteeData[] $invitees
     */
    public function setInvitees(array $invitees)
    {
        $this->invitees = $invitees;
    }

    /**
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param string $location
     */
    public function setLocation($location)
    {
        $this->location = $location;
    }

    /**
     * @return string
     */
    public function getOwnerEmailAddress()
    {
        return $this->ownerEmailAddress;
    }

    /**
     * @param string $ownerEmailAddress
     */
    public function setOwnerEmailAddress($ownerEmailAddress)
    {
        $this->ownerEmailAddress = $ownerEmailAddress;
    }

    /**
     * @return string
     */
    public function getOwnerIdentity()
    {
        return $this->ownerIdentity;
    }

    /**
     * @param string $ownerIdentity
     */
    public function setOwnerIdentity($ownerIdentity)
    {
        $this->ownerIdentity = $ownerIdentity;
    }

    /**
     * @return string
     */
    public function getOwnerName()
    {
        return $this->ownerName;
    }

    /**
     * @param string $ownerName
     */
    public function setOwnerName($ownerName)
    {
        $this->ownerName = $ownerName;
    }

    /**
     * @return DateTimeInterface
     */
    public function getRepetitionEnds()
    {
        return $this->repetitionEnds;
    }

    /**
     * @param DateTimeInterface $repetitionEnds
     */
    public function setRepetitionEnds($repetitionEnds)
    {
        $this->repetitionEnds = $repetitionEnds;
    }

    /**
     * @return string
     */
    public function getRepetitionType()
    {
        return $this->repetitionType;
    }

    /**
     * @param string $repetitionType
     */
    public function setRepetitionType($repetitionType)
    {
        $this->repetitionType = $repetitionType;
    }

    /**
     * @return string
     */
    public function getTenantId()
    {
        return $this->tenantId;
    }

    /**
     * @param string $tenantId
     */
    public function setTenantId($tenantId)
    {
        $this->tenantId = $tenantId;
    }

    /**
     * @return DateTimeInterface
     */
    public function getTimeSpanBegins()
    {
        return $this->timeSpanBegins;
    }

    /**
     * @param DateTimeInterface $timeSpanBegins
     */
    public function setTimeSpanBegins($timeSpanBegins)
    {
        $this->timeSpanBegins = $timeSpanBegins;
    }

    /**
     * @return DateTimeInterface
     */
    public function getTimeSpanEnds()
    {
        return $this->timeSpanEnds;
    }

    /**
     * @param DateTimeInterface $timeSpanEnds
     */
    public function setTimeSpanEnds($timeSpanEnds)
    {
        $this->timeSpanEnds = $timeSpanEnds;
    }
}
