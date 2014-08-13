<?php

namespace SaasOvation\IdentityAccess\Test\Resource;

use SaasOvation\Common\Media\RepresentationReader;
use SaasOvation\IdentityAccess\Domain\Model\DomainRegistry;

class GroupResourceTest extends ResourceTestCase
{
    public function testGetGroup()
    {
        $group = $this->group1Aggregate();
        DomainRegistry::groupRepository()->add($group);

        $client = static::createClient();
        $client->request(
            'GET', sprintf('/tenants/%s/groups/%s', $group->tenantId()->id(), $group->name())
        );

        $aResponse = $client->getResponse()->getContent();
        $reader = RepresentationReader::fromString($aResponse);

        $this->assertEquals($group->tenantId()->id(), $reader->stringValue('tenant_id.id'));
        $this->assertEquals($group->name(), $reader->stringValue('name'));
    }
}
