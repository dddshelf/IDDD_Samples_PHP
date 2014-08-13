<?php

namespace SaasOvation\IdentityAccess\Test\Resource;

use SaasOvation\Common\Media\RepresentationReader;

class TenantResourceTest extends ResourceTestCase
{
    public function testGetTenant()
    {
        $tenant = $this->tenantAggregate();

        $client = static::createClient();

        $client->request(
            'GET',
            sprintf('/tenants/%s', $tenant->tenantId()->id())
        );

        $reader = RepresentationReader::fromString(
            $client->getResponse()->getContent()
        );

        $this->assertEquals($tenant->name(), $reader->stringValue('name'));
        $this->assertTrue($reader->booleanValue('active'));
    }
}
