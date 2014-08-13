<?php

namespace SaasOvation\Common\Test;

use PHPUnit_Framework_TestCase;
use SaasOvation\Common\Domain\Model\DomainEventPublisher;

abstract class CommonTestCase extends PHPUnit_Framework_TestCase
{
    use BuildsServiceContainer;

    protected function setUp()
    {
        DomainEventPublisher::instance()->reset();

        $this->buildAndCompileServiceContainer(
            [
                __DIR__ . '/../Resources/config',
            ],
            [
                'common.xml',
                'common-doctrine.xml',
            ]
        );

        $this->container->get('doctrine.orm.entity_manager')->beginTransaction();
    }

    protected function tearDown()
    {
        $this->container->get('doctrine.orm.entity_manager')->rollback();
    }
}
