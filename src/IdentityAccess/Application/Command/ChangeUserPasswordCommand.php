<?php

namespace SaasOvation\IdentityAccess\Application\Command;

class ChangeUserPasswordCommand
{
    private $tenantId;
    private $username;
    private $currentPassword;
    private $changedPassword;

    public function __construct($tenantId, $aUsername,
            $aCurrentPassword, $aChangedPassword)
    {
        $this->tenantId = $tenantId;
        $this->username = $aUsername;
        $this->currentPassword = $aCurrentPassword;
        $this->changedPassword = $aChangedPassword;
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

    public function getCurrentPassword()
    {
        return $this->currentPassword;
    }

    public function setCurrentPassword($currentPassword)
    {
        $this->currentPassword = $currentPassword;
    }

    public function getChangedPassword()
    {
        return $this->changedPassword;
    }

    public function setChangedPassword($changedPassword)
    {
        $this->changedPassword = $changedPassword;
    }
}
