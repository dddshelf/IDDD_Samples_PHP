<?php

namespace SaasOvation\IdentityAccess\Domain\Model\Identity;

use Doctrine\Common\Collections\Collection;

interface GroupRepository
{
    /**
     * @param Group $aGroup
     *
     * @return void
     */
    public function add(Group $aGroup);

    /**
     * @param TenantId $aTenantId
     *
     * @return Collection
     */
    public function allGroups(TenantId $aTenantId);

    /**
     * @param TenantId $aTenantId
     * @param string $aName
     *
     * @return Group
     */
    public function groupNamed(TenantId $aTenantId, $aName);

    /**
     * @param Group $aGroup
     *
     * @return void
     */
    public function remove(Group $aGroup);
}
