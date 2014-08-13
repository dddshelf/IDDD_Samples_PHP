<?php

use SaasOvation\Common\Test\BuildsServiceContainer;
use Doctrine\ORM\Tools\Console\ConsoleRunner;

class ServiceContainerBuilder
{
    use BuildsServiceContainer;

    public function get($aServiceId)
    {
        if (null === $this->container) {
            $this->buildAndCompileServiceContainer(
                [
                    __DIR__ . '/src/IdentityAccess/Resources/config',
                ],
                [
                    'identityaccess.xml',
                    'identityaccess-application.xml',
                    'doctrine.xml',
                ]
            );
        }

        return $this->container->get($aServiceId);
    }
}

$builder = new ServiceContainerBuilder();

return ConsoleRunner::createHelperSet($builder->get('doctrine.orm.entity_manager'));