<?php

namespace SaasOvation\IdentityAccess\Domain\Model\Identity\GroupMemberType;

use SaasOvation\IdentityAccess\Domain\Model\Identity\GroupMemberType;

class User extends GroupMemberType
{
    public function isUser()
    {
        return true;
    }
}
