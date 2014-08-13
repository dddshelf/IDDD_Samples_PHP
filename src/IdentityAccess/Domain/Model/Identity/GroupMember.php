<?php

namespace SaasOvation\IdentityAccess\Domain\Model\Identity;

use SaasOvation\Common\Domain\Model\IdentifiedValueObject;

class GroupMember extends IdentifiedValueObject
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var TenantId
     */
    private $tenantId;

    /**
     * @var GroupMemberType
     */
    private $type;
    
    public function isGroup()
    {
        return $this->type()->isGroup();
    }

    public function isUser()
    {
        return $this->type()->isUser();
    }

    public function name()
    {
        return $this->name;
    }

    public function tenantId()
    {
        return $this->tenantId;
    }

    public function type()
    {
        return $this->type;
    }

    public function equals($anObject)
    {
        $equalObjects = false;

        if (null !== $anObject && get_class($this) === get_class($anObject)) {
            $equalObjects =
                $this->tenantId()->equals($anObject->tenantId()) &&
                $this->name() === $anObject->name() &&
                $this->type() == $anObject->type();
        }

        return $equalObjects;
    }

    public function __toString()
    {
        return  'GroupMember [name= '  . $this->name  .  ', tenantId= '  . $this->tenantId  .  ', type= '  . $this->type  .  '] ';
    }

    public function __construct(TenantId $aTenantId, $aName, GroupMemberType $aType)
    {
        $this->setName($aName);
        $this->setTenantId($aTenantId);
        $this->setType($aType);
    }

    protected function setName($aName)
    {
        $this->assertArgumentNotEmpty($aName, 'Member name is required.');
        $this->assertArgumentLength($aName, 1, 100, 'Member name must be 100 characters or less.');

        $this->name = $aName;
    }

    protected function setTenantId(TenantId $aTenantId)
    {
        $this->assertArgumentNotNull($aTenantId, 'The tenantId must be provided.');

        $this->tenantId = $aTenantId;
    }

    protected function setType(GroupMemberType $aType)
    {
        $this->assertArgumentNotNull($aType, 'The type must be provided.');

        $this->type = $aType;
    }
}
