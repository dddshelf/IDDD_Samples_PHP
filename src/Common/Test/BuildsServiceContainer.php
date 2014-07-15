<?php

namespace SaasOvation\Common\Test;

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
    protected function buildAndCompileServiceContainer($paths, $files)
    {
        $this->container = new ContainerBuilder();

        $fileLocator = new FileLocator($paths);

        $loader = new DelegatingLoader(
            new LoaderResolver([
                new XmlFileLoader($this->container, $fileLocator)]
            )
        );

        foreach ($files as $aResource) {
            $loader->load($aResource);
        }

        $this->container->compile();
    }
}