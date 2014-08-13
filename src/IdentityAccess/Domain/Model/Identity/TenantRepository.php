<?php

namespace SaasOvation\IdentityAccess\Domain\Model\Identity;

interface TenantRepository
{
    /**
     * @param Tenant $aTenant
     *
     * @return void
     */
    public function add(Tenant $aTenant);

    /**
     * @return TenantId
     */
    public function nextIdentity();

    /**
     * @param Tenant $aTenant
     *
     * @return void
     */
    public function remove(Tenant $aTenant);

    /**
     * @param string $aName
     *
     * @return Tenant
     */
    public function tenantNamed($aName);

    /**
     * @param TenantId $aTenantId
     *
     * @return Tenant
     */
    public function tenantOfId(TenantId $aTenantId);
}
