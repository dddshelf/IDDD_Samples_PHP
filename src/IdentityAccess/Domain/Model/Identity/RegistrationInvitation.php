<?php

namespace SaasOvation\IdentityAccess\Domain\Model\Identity;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use LogicException;
use SaasOvation\Common\Domain\Model\ConcurrencySafeEntity;

class RegistrationInvitation extends ConcurrencySafeEntity
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

    public function description()
    {
        return $this->description;
    }

    public function invitationId()
    {
        return $this->invitationId;
    }

    public function isAvailable()
    {
        $isAvailable = false;

        if (null === $this->startingOn() && null === $this->until()) {
            $isAvailable = true;
        } else {
            $time = (new DateTime())->getTimestamp();
            if ($time >= $this->startingOn()->getTimestamp() && $time <= $this->until()->getTimestamp()) {
                $isAvailable = true;
            }
        }

        return $isAvailable;
    }

    public function isIdentifiedBy($anInvitationIdentifier)
    {
        $isIdentified = $anInvitationIdentifier === $this->invitationId();

        if (!$isIdentified && null !== $this->description()) {
            $isIdentified = $anInvitationIdentifier === $this->description();
        }

        return $isIdentified;
    }

    public function openEnded()
    {
        $this->setStartingOn(null);
        $this->setUntil(null);

        return $this;
    }

    public function redefineAs()
    {
        $this->setStartingOn(null);
        $this->setUntil(null);

        return $this;
    }

    public function startingOn(DateTimeInterface $aDate = null)
    {
        if (null === $aDate) {
            return $this->startingOn;
        }

        if (null !== $this->until()) {
            throw new LogicException('Cannot set starting-on date after until date.');
        }

        $this->setStartingOn($aDate);

        // temporary if until() properly follows, but
        // prevents illegal state if until() doesn't follow
        $this->setUntil((new DateTimeImmutable())->setTimestamp($aDate->getTimestamp() + 86400000));

        return $this;
    }

    public function tenantId()
    {
        return $this->tenantId;
    }

    public function toDescriptor()
    {
        return new InvitationDescriptor(
            $this->tenantId(),
            $this->invitationId(),
            $this->description(),
            $this->startingOn(),
            $this->until()
        );
    }

    public function until(DateTimeImmutable $aDate = null)
    {
        if (null === $aDate) {
            return $this->until;
        }

        if ($this->startingOn() === null) {
            throw new LogicException('Cannot set until date before setting starting-on date.');
        }

        $this->setUntil($aDate);

        return $this;
    }

    public function equals($anObject)
    {
        $equalObjects = false;

        if ($anObject !== null && get_class($this) === get_class($anObject)) {
            $equalObjects =
                $this->tenantId()->equals($anObject->tenantId()) &&
                $this->invitationId()->equals($anObject->invitationId());
        }

        return $equalObjects;
    }
    
    public function __toString()
    {
        return 'RegistrationInvitation ['
        . 'tenantId=' . $this->tenantId
        . ', description=' . $this->description
        . ', invitationId=' . $this->invitationId
        . ', startingOn=' . $this->startingOn->format('Y-m-d H:i:s')
        . ', until=' . $this->until->format('Y-m-d H:i:s') . ']';
    }

    public function __construct(
        TenantId $aTenantId,
        $anInvitationId,
        $aDescription
    ) {
        $this->setDescription($aDescription);
        $this->setInvitationId($anInvitationId);
        $this->setTenantId($aTenantId);

        $this->assertValidInvitationDates();
    }

    protected function assertValidInvitationDates()
    {
        // either both dates must be null, or both dates must be set
        if (null === $this->startingOn() && null === $this->until()) {
            ; // valid
        } else if ($this->startingOn() === null || $this->until() === null &&
            $this->startingOn() != $this->until()) {
            throw new LogicException('This is an invalid open-ended invitation.');
        } else if ($this->startingOn()> $this->until()) {
            throw new LogicException('The starting date and time must be before the until date and time.');
        }
    }

    protected function setDescription($aDescription)
    {
        $this->assertArgumentNotEmpty($aDescription, 'The invitation description is required.');
        $this->assertArgumentLength($aDescription, 1, 100, 'The invitation description must be 100 characters or less.');

        $this->description = $aDescription;
    }

    protected function setInvitationId($anInvitationId)
    {
        $this->assertArgumentNotEmpty($anInvitationId, 'The invitationId is required.');
        $this->assertArgumentLength($anInvitationId, 1, 36, 'The invitation id must be 36 characters or less.');

        $this->invitationId = $anInvitationId;
    }

    protected function setStartingOn(DateTimeInterface $aStartingOn = null)
    {
        $this->startingOn = $aStartingOn;
    }

    protected function setTenantId(TenantId $aTenantId)
    {
        $this->assertArgumentNotNull($aTenantId, 'The tenantId is required.');

        $this->tenantId = $aTenantId;
    }

    protected function setUntil(DateTimeInterface $anUntil = null)
    {
        $this->until = $anUntil;
    }
}
