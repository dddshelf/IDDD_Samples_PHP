<?php

namespace SaasOvation\IdentityAccess\Domain\Model\Identity;

use DateTimeInterface;
use SaasOvation\Common\AssertionConcern;

final class InvitationDescriptor extends AssertionConcern
{
    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $invitationId;

    /**
     * @var DateTimeInterface
     */
    private $startingOn;

    /**
     * @var TenantId
     */
    private $tenantId;

    /**
     * @var DateTimeInterface
     */
    private $until;
    
    public function __construct(
        TenantId $aTenantId,
        $anInvitationId,
        $aDescription,
        DateTimeInterface $aStartingOn = null,
        DateTimeInterface $anUntil = null
    ) {
        $this->setDescription($aDescription);
        $this->setInvitationId($anInvitationId);
        $this->setStartingOn($aStartingOn);
        $this->setTenantId($aTenantId);
        $this->setUntil($anUntil);
    }

    public function description()
    {
        return $this->description;
    }

    public function invitationId()
    {
        return $this->invitationId;
    }

    public function isOpenEnded()
    {
        return null === $this->startingOn() && null === $this->until();
    }

    public function startingOn()
    {
        return $this->startingOn;
    }

    public function tenantId()
    {
        return $this->tenantId;
    }

    public function until()
    {
        return $this->until;
    }

    public function equals($anObject)
    {
        $equalObjects = false;

        if (null !== $anObject && get_class($this) === get_class($anObject)) {
            $equalObjects =
                $this->tenantId() === $anObject->tenantId() &&
                $this->invitationId() === $anObject->invitationId() &&
                $this->description() === $anObject->description() &&
                ((null === $this->startingOn() && null === $anObject->startingOn()) ||
                    (null !== $this->startingOn() && $this->startingOn() == $anObject->startingOn())) &&
                (($this->until() === null && $anObject->until() === null) ||
                    ($this->until() !== null && $this->until() == $anObject->until()));
        }

        return $equalObjects;
    }

    public function __toString()
    {
        return 'InvitationDescriptor [tenantId=' . $this->tenantId
        . ', invitationId=' . $this->invitationId
        . ', description=' . $this->description
        . ', startingOn=' . $this->startingOn->format('Y/m/d H:i:s') . ', until=' . $this->until->format('Y/m/d H:i:s') . ']';
    }

    private function setDescription($aDescription)
    {
        $this->assertArgumentNotEmpty($aDescription, 'The invitation description is required.');

        $this->description = $aDescription;
    }

    private function setInvitationId($anInvitationId)
    {
        $this->assertArgumentNotEmpty($anInvitationId, 'The invitationId is required.');

        $this->invitationId = $anInvitationId;
    }

    private function setStartingOn(DateTimeInterface $aStartingOn = null)
    {
        $this->startingOn = $aStartingOn;
    }

    private function setTenantId(TenantId $aTenantId)
    {
        $this->assertArgumentNotNull($aTenantId, 'The tenantId is required.');

        $this->tenantId = $aTenantId;
    }

    private function setUntil(DateTimeInterface $anUntil = null)
    {
        $this->until = $anUntil;
    }
}
