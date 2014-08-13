<?php

namespace SaasOvation\IdentityAccess\Domain\Model\Identity\GroupMemberType;

use SaasOvation\IdentityAccess\Domain\Model\Identity\GroupMemberType;

class Group extends GroupMemberType
{
    public function isGroup()
    {
        return true;
    }
}
