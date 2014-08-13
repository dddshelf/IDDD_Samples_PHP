<?php

namespace SaasOvation\IdentityAccess\Test\Domain\Model;

use Doctrine\ORM\EntityManager;
use PHPUnit_Framework_TestCase;
use SaasOvation\Common\Domain\Model\DomainEventPublisher;
use SaasOvation\Common\Test\BuildsServiceContainer;

abstract class DomainTest extends PHPUnit_Framework_TestCase
{
    use BuildsServiceContainer;

    /**
     * @var EntityManager
     */
    private $entityManager;

    protected function entityManager()
    {
        return $this->entityManager;
    }

    protected function setUp()
    {
        $this->buildAndCompileServiceContainer(
            [
                __DIR__ . '/../../../Resources/config',
                __DIR__ . '/../../Resources/config',
                __DIR__ . '/../../../../Common/Resources/config'
            ],
            [
                'identityaccess.xml',
                'identityaccess-doctrine.xml',
                'common.xml'
            ]
        );

        $this->entityManager = $this->container->get('doctrine.orm.entity_manager');

        $this->entityManager()->beginTransaction();

        DomainEventPublisher::instance()->reset();
    }

    protected function tearDown()
    {
        $this->entityManager()->rollback();
        $this->entityManager()->getConnection()->close();
        $this->entityManager()->close();

        $this->entityManager = null;
    }
}
