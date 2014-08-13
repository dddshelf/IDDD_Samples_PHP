<?php

namespace SaasOvation\IdentityAccess\Resource\APIBundle\Controller;

use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations\View;
use SaasOvation\IdentityAccess\Application\ApplicationServiceRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NotificationsController extends AbstractResource
{
    /**
     * @View
     * @Cache(maxage="60")
     */
    public function getNotificationsAction()
    {
        $currentNotificationLog = $this->notificationApplicationService()->currentNotificationLog();

        if (null === $currentNotificationLog) {
            throw new NotFoundHttpException();
        }

        return $currentNotificationLog;
    }

    /**
     * @View
     * @Cache(maxage="3600")
     */
    public function getNotificationAction($notificationId)
    {
        $notificationLog = $this->notificationApplicationService()->notificationLog($notificationId);

        if (null === $notificationLog) {
            throw new NotFoundHttpException();
        }

        return $notificationLog;
    }
}
