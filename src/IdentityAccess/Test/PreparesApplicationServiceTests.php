<?php

namespace SaasOvation\IdentityAccess\Test;

use SaasOvation\Common\Domain\Model\DomainEventPublisher;
use SaasOvation\Common\Event\EventStore;
use SaasOvation\Common\Test\BuildsServiceContainer;
use SaasOvation\IdentityAccess\Application\ApplicationServiceRegistry;
use SaasOvation\IdentityAccess\Domain\Model\DomainRegistry;

trait PreparesApplicationServiceTests
{
    use BuildsServiceContainer;

    /**
     * @var EventStore
     */
    protected $eventStore;

    protected function setUp()
    {
        DomainEventPublisher::instance()->reset();

        $this->buildAndCompileServiceContainer(
            [
                __DIR__ . '/../Resources/config',
                __DIR__ . '/Resources/config',
                __DIR__ . '/../../Common/Resources/config'
            ],
            [
                'common.xml',
                'identityaccess-application.xml',
                'identityaccess-test.xml'
            ]
        );

        DomainRegistry::setContainer($this->container);
        ApplicationServiceRegistry::setContainer($this->container);

        $this->eventStore = $this->container->get('eventStore');

        $this->clean($this->eventStore);
        $this->clean(DomainRegistry::groupRepository());
        $this->clean(DomainRegistry::roleRepository());
        $this->clean(DomainRegistry::tenantRepository());
        $this->clean(DomainRegistry::userRepository());
    }

    protected function tearDown()
    {
        $this->clean($this->eventStore);
        $this->clean(DomainRegistry::groupRepository());
        $this->clean(DomainRegistry::roleRepository());
        $this->clean(DomainRegistry::tenantRepository());
        $this->clean(DomainRegistry::userRepository());
    }

    private function clean($aCleanableStore)
    {
        $aCleanableStore->clean();
    }
}