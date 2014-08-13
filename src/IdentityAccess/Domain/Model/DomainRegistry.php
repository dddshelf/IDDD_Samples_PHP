<?php

namespace SaasOvation\IdentityAccess\Domain\Model;

use SaasOvation\IdentityAccess\Domain\Model\Identity\TenantProvisioningService;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DomainRegistry
{
    /**
     * @var ContainerInterface
     */
    private static $container;

    public static function authenticationService()
    {
        return static::$container->get('authenticationService');
    }

    public static function authorizationService()
    {
        return static::$container->get('authorizationService');
    }

    public static function encryptionService()
    {
        return static::$container->get('encryptionService');
    }

    public static function groupMemberService()
    {
        return static::$container->get('groupMemberService');
    }

    public static function groupRepository()
    {
        return static::$container->get('groupRepository');
    }

    public static function passwordService()
    {
        return static::$container->get('passwordService');
    }

    public static function roleRepository()
    {
        return static::$container->get('roleRepository');
    }

    public static function tenantProvisioningService()
    {
        return static::$container->get('tenantProvisioningService');
    }

    public static function tenantRepository()
    {
        return static::$container->get('tenantRepository');
    }

    public static function userRepository()
    {
        return static::$container->get('userRepository');
    }

    public static function setContainer(ContainerInterface $container)
    {
        self::$container = $container;
    }
}
