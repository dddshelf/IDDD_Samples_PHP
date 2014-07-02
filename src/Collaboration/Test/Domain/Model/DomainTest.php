<?php

namespace SaasOvation\Collaboration\Test\Domain\Model;

use PDO;
use SaasOvation\Collaboration\Test\BuildsAggregates;
use SaasOvation\Collaboration\Test\BuildsServiceContainer;
use SaasOvation\Collaboration\Test\StorageCleaner;
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
            $this->buildAndCompileServiceContainer();

            // Initialize the FollowStoreEventDispatcher by requesting it to the service container
            $this->container->get('followStoreEventDispatcher');

            // Initialize the RabbitMQ Event Dispatcher
            $this->container->get('rabbitMQEventDispatcher');
        }

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