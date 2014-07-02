<?php

namespace SaasOvation\Collaboration\Test;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

trait BuildsServiceContainer
{
    /**
     * @var ContainerBuilder
     */
    protected $container;

    /**
     * The order of load is important. The test services in collaboration-test.xml will
     * replace the standard ones in the standard collaboration.xml definitions.
     * Allows for mocking some heavy interfaces.
     *
     * @throws \Symfony\Component\Config\Exception\FileLoaderLoadException
     */
    protected function buildAndCompileServiceContainer()
    {
        $this->container = new ContainerBuilder();

        $fileLocator = new FileLocator([
            __DIR__ . '/../Resources/config',
            __DIR__ . '/Resources/config'
        ]);

        $loader = new DelegatingLoader(
            new LoaderResolver([
                new XmlFileLoader($this->container, $fileLocator)]
            )
        );

        foreach (['collaboration.xml', 'collaboration-test.xml'] as $aResource) {
            $loader->load($aResource);
        }

        $this->container->compile();
    }
}