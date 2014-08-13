<?php

namespace SaasOvation\IdentityAccess\Domain\Model\Identity;

class GroupMemberType
{
    public function isGroup()
    {
        return false;
    }

    public function isUser()
    {
        return false;
    }
}