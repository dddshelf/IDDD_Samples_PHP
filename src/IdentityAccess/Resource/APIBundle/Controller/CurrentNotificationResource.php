<?php

namespace SaasOvation\IdentityAccess\Resource\APIBundle\Controller;

use FOS\RestBundle\Controller\Annotations\Prefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;

use FOS\RestBundle\View\View;
use SaasOvation\Common\Media\Link;
use SaasOvation\Common\Media\OvationsMediaType;
use SaasOvation\Common\Notification\NotificationLog;
use SaasOvation\Common\Serializer\ObjectSerializer;
use SaasOvation\IdentityAccess\Application\Representation\NotificationLogRepresentation;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @RouteResource("CurrentNotification")
 */
class CurrentNotificationResource extends AbstractResource
{
    /**
     * @Cache(max-age="60")
     */
    public function getCurrentNotificationLog()
    {
        $currentNotificationLog = $this->notificationApplicationService()->currentNotificationLog();

        if (null === $currentNotificationLog) {
            throw new NotFoundHttpException();
        }

        return $this->handleView(View::create($currentNotificationLog, 200));
    }
}
