<?php

namespace SaasOvation\Collaboration\Test\Domain\Model;

use PDO;
use SaasOvation\Collaboration\Domain\Model\DomainRegistry;
use SaasOvation\Collaboration\Test\BuildsAggregates;
use SaasOvation\Collaboration\Test\StorageCleaner;
use SaasOvation\Common\Test\BuildsServiceContainer;
use SaasOvation\Common\Test\Domain\Model\EventTrackingTestCase;

abstract class DomainTest extends EventTrackingTestCase
{
    use BuildsAggregates;
    use BuildsServiceContainer;

    /**
     * @var PDO
     */
    private $dataSource;

    /**
     * @var StorageCleaner
     */
    private $storageCleaner;

    protected function setUp()
    {
        if (null === $this->container) {
            $this->buildAndCompileServiceContainer(
                [
                    __DIR__ . '/../../../Resources/config',
                    __DIR__ . '/../../Resources/config'
                ],
                [
                    'collaboration.xml',
                    'collaboration-test.xml'
                ]
            );

            // Initialize the FollowStoreEventDispatcher by requesting it to the service container
            $this->container->get('followStoreEventDispatcher');

            // Initialize the RabbitMQ Event Dispatcher
            $this->container->get('rabbitMQEventDispatcher');
        }

        DomainRegistry::setContainer($this->container);

        if (null === $this->dataSource) {
            $this->dataSource = $this->container->get('collaborationDataSource');
        }

        $this->storageCleaner = new StorageCleaner($this->dataSource);

        parent::setUp();
    }

    protected function tearDown()
    {
        $this->storageCleaner->clean();

        $this->dataSource = null;

        parent::tearDown();
    }
}
