<?php

namespace SaasOvation\Collaboration\Test\Domain\Model\Forum;

use SaasOvation\Collaboration\Domain\Model\DomainRegistry;
use SaasOvation\Collaboration\Domain\Model\Forum\Forum;
use SaasOvation\Collaboration\Test\Domain\Model\DomainTest;
use SaasOvation\Collaboration\Domain\Model\Collaborator\Creator;
use SaasOvation\Collaboration\Domain\Model\Collaborator\Moderator;
use SaasOvation\Collaboration\Domain\Model\Tenant\Tenant;
use SaasOvation\Common\Domain\Model\DomainEventPublisher;

abstract class AbstractForumTest extends DomainTest
{
    protected function forumAggregate()
    {
        return Forum::create(
            new Tenant('01234567'),
            DomainRegistry::forumRepository()->nextIdentity(),
            new Creator('jdoe', 'John Doe', 'jdoe@saasovation.com'),
            new Moderator('jdoe', 'John Doe', 'jdoe@saasovation.com'),
            'John Doe Does DDD',
            'A set of discussions about DDD for anonymous developers.',
            null
        );
    }

    protected function setUp()
    {
        DomainEventPublisher::instance()->reset();

        parent::setUp();
    }
}
