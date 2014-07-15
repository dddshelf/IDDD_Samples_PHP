<?php

namespace SaasOvation\Common\Test\Media;

use PHPUnit_Framework_TestCase;
use SaasOvation\Common\Media\RepresentationReader;

class RepresentationReaderTest extends PHPUnit_Framework_TestCase
{
    private static $USER_IN_ROLE_REPRESENTATION = <<<EOJSON
{
    "role": "Author",
    "username": "zoe",
    "tenantId": "A94A8298-43B8-4DA0-9917-13FFF9E116ED",
    "firstName": "Zoe",
    "lastName": "Doe",
    "emailAddress": "zoe@saasovation.com"
}
EOJSON;

    public function testUserInRoleRepresentation()
    {
        $reader = RepresentationReader::fromString(static::$USER_IN_ROLE_REPRESENTATION);

        $this->assertEquals('Author', $reader->stringValue('role'));
        $this->assertEquals('zoe', $reader->stringValue('username'));
        $this->assertEquals('A94A8298-43B8-4DA0-9917-13FFF9E116ED', $reader->stringValue('tenantId'));
        $this->assertEquals('Zoe', $reader->stringValue('firstName'));
        $this->assertEquals('Doe', $reader->stringValue('lastName'));
        $this->assertEquals('zoe@saasovation.com', $reader->stringValue('emailAddress'));
    }
}
