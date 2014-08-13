<?php

namespace SaasOvation\IdentityAccess\Resource\APIBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use SaasOvation\IdentityAccess\Application\ApplicationServiceRegistry;

class AbstractResource extends FOSRestController
{
    protected function accessApplicationService()
    {
        return ApplicationServiceRegistry::accessApplicationService();
    }

    protected function identityApplicationService()
    {
        return ApplicationServiceRegistry::identityApplicationService();
    }

    protected function notificationApplicationService()
    {
        return ApplicationServiceRegistry::notificationApplicationService();
    }
}
