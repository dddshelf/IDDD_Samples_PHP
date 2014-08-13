<?php

namespace SaasOvation\IdentityAccess\Resource\APIBundle\Controller;

use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TenantsController extends AbstractResource
{
    /**
     * @Cache(maxAge="3600")
     */
    public function getTenantAction($aTenantId)
    {
        $aTenant = $this->identityApplicationService()->tenant($aTenantId);

        if (null === $aTenant) {
            throw new NotFoundHttpException();
        }

        return $aTenant;
    }
}
