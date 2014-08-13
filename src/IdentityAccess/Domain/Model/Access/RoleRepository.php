<?php

namespace SaasOvation\IdentityAccess\Domain\Model\Access;

use SaasOvation\IdentityAccess\Domain\Model\Identity\TenantId;

interface RoleRepository
{
    public function add(Role $aRole);

    public function allRoles(TenantId $aTenantId);

    public function remove(Role $aRole);

    public function roleNamed(TenantId $aTenantId, $aRoleName);
}
