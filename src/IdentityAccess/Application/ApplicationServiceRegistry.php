<?php

namespace SaasOvation\IdentityAccess\Application;

use Symfony\Component\DependencyInjection\ContainerInterface;

class ApplicationServiceRegistry
{
    /**
     * @var ContainerInterface
     */
    private static $container;

    public static function accessApplicationService()
    {
        return self::$container->get('accessApplicationService');
    }

    public static function identityApplicationService()
    {
        return self::$container->get('identityApplicationService');
    }

    public static function notificationApplicationService()
    {
        return self::$container->get('notificationApplicationService');
    }

    public static function setContainer(ContainerInterface $aServiceContainer)
    {
        self::$container = $aServiceContainer;
    }
}
