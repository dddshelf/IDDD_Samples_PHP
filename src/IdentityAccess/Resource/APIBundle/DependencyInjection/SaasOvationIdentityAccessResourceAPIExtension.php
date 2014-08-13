<?php

namespace SaasOvation\IdentityAccess\Resource\APIBundle\DependencyInjection;

use SaasOvation\IdentityAccess\Application\ApplicationServiceRegistry;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class SaasOvationIdentityAccessResourceAPIExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader(
            $container,
            new FileLocator(
                [
                    __DIR__ . '/../Resources/config',
                    __DIR__ . '/../../../Resources/config',
                    __DIR__ . '/../../../Test/Resources/config',
                    __DIR__ . '/../../../../Common/Resources/config',
                ]
            )
        );

        $loader->load('common.xml');
        $loader->load('identityaccess-application.xml');
        $loader->load('identityaccess-test.xml');

        ApplicationServiceRegistry::setContainer($container);
    }
}
