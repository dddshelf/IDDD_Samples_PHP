<?php

namespace SaasOvation\IdentityAccess\Application\Command;

class AddGroupToGroupCommand
{
    /**
     * @var string
     */
    private $tenantId;

    /**
     * @var string
     */
    private $childGroupName;

    /**
     * @var string
     */
    private $parentGroupName;

    public function __construct($tenantId, $parentGroupName, $childGroupName)
    {
        $this->tenantId = $tenantId;
        $this->parentGroupName = $parentGroupName;
        $this->childGroupName = $childGroupName;
    }

    public function getTenantId()
    {
        return $this->tenantId;
    }

    public function setTenantId($tenantId)
    {
        $this->tenantId = $tenantId;
    }

    public function getChildGroupName()
    {
        return $this->childGroupName;
    }

    public function setChildGroupName($childGroupName)
    {
        $this->childGroupName = $childGroupName;
    }

    public function getParentGroupName()
    {
        return $this->parentGroupName;
    }

    public function setParentGroupName($parentGroupName)
    {
        $this->parentGroupName = $parentGroupName;
    }
}
