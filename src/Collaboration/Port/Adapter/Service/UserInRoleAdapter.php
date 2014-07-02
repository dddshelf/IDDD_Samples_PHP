<?php

namespace SaasOvation\Collaboration\Port\Adapter\Service;

use SaasOvation\Collaboration\Domain\Model\Tenant\Tenant;

interface UserInRoleAdapter
{
    public function toCollaborator(Tenant $aTenant, $anIdentity, $aRoleName, $aCollaboratorClass);
}