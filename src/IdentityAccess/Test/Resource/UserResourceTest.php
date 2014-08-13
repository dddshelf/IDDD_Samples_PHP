<?php

namespace SaasOvation\IdentityAccess\Test\Resource;

use Rhumsaa\Uuid\Uuid;
use SaasOvation\Common\Media\RepresentationReader;
use SaasOvation\IdentityAccess\Domain\Model\DomainRegistry;
use SaasOvation\IdentityAccess\Domain\Model\Access\Role;
use SaasOvation\IdentityAccess\Domain\Model\Identity\User;

class UserResourceTest extends ResourceTestCase
{
    public function testGetAuthenticUser()
    {
        $user = $this->userAggregate();
        DomainRegistry::userRepository()->add($user);

        $client = static::createClient();

        $client->request(
            'GET',
            sprintf(
                '/tenants/%s/users/%s/autenticatedWith/%s',
                $user->tenantId()->id(),
                $user->username(),
                self::$FIXTURE_PASSWORD
            )
        );

        $aJSONRepresentation = $client->getResponse()->getContent();

        $reader = RepresentationReader::fromString(
            $aJSONRepresentation
        );

        $this->assertEquals($user->tenantId()->id(), $reader->stringValue('tenant_id.id'));
        $this->assertEquals($user->username(), $reader->stringValue('username'));
        $this->assertEquals($user->person()->emailAddress()->address(), $reader->stringValue('email_address'));
    }

    public function testGetAuthenticUserWrongPassword()
    {
        $user = $this->userAggregate();
        DomainRegistry::userRepository()->add($user);

        $client = static::createClient();

        $client->request(
            'GET',
            sprintf(
                '/tenants/%s/users/%s/autenticatedWith/%s',
                $user->tenantId()->id(),
                $user->username(),
                Uuid::uuid4()->toString()
            )
        );

        $anStatus = $client->getResponse()->getStatusCode();
        $this->assertTrue(404 === $anStatus || 500 === $anStatus);
    }

    public function testGetUser()
    {
        $user = $this->userAggregate();
        DomainRegistry::userRepository()->add($user);

        $client = static::createClient();

        $client->request(
            'GET',
            sprintf(
                '/tenants/%s/users/%s',
                $user->tenantId()->id(),
                $user->username()
            )
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $aJSONRepresentation = $client->getResponse()->getContent();

        $reader = RepresentationReader::fromString(
            $aJSONRepresentation
        );

        $this->assertEquals($user->username(), $reader->stringValue('username'));
        $this->assertTrue($reader->booleanValue('enablement.enabled'));
    }

    public function testGetNonExistingUser()
    {
        $user = $this->userAggregate();
        DomainRegistry::userRepository()->add($user);

        $client = static::createClient();

        $client->request(
            'GET',
            sprintf(
                '/tenants/%s/users/%s',
                $user->tenantId()->id(),
                $user->username() . '!'
            )
        );

        $this->assertThat(
            $client->getResponse()->getStatusCode(),
            $this->logicalOr(
                $this->equalTo(404),
                $this->equalTo(500)
            )
        );
    }

    public function testIsUserInRole()
    {
        $user = $this->userAggregate();
        DomainRegistry::userRepository()->add($user);

        $role = $this->roleAggregate();
        $role->assignUser($user);
        DomainRegistry::roleRepository()->add($role);

        $client = static::createClient();

        $client->request(
            'GET',
            sprintf(
                '/tenants/%s/users/%s/inroles/%s',
                $user->tenantId()->id(),
                $user->username(),
                $role->name()
            )
        );

        $reader = RepresentationReader::fromString(
            $client->getResponse()->getContent()
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $this->assertEquals($user->username(),  $reader->stringValue('username'));
        $this->assertEquals($role->name(), $reader->stringValue('role'));
    }

    public function testIsUserNotInRole()
    {
        $user = $this->userAggregate();
        DomainRegistry::userRepository()->add($user);

        $role = $this->roleAggregate();
        DomainRegistry::roleRepository()->add($role);

        $client = static::createClient();

        $client->request(
            'GET',
            sprintf(
                '/tenants/%s/users/%s/inroles/%s',
                $user->tenantId()->id(),
                $user->username(),
                $role->name()
            )
        );

        $this->assertEquals(204, $client->getResponse()->getStatusCode());
    }
}
