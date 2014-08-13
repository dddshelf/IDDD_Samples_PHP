<?php

namespace SaasOvation\IdentityAccess\Resource\APIBundle\Controller;

use FOS\RestBundle\Controller\Annotations\Prefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;;

class GroupsController extends AbstractResource
{
    /**
     * @View
     * @Cache(maxage="30")
     */
    public function getGroupAction($tenantId, $groupName)
    {
        $group = $this->identityApplicationService()->group($tenantId, $groupName);

        if (null === $group) {
            throw new NotFoundHttpException();
        }

        return $group;
    }
}
