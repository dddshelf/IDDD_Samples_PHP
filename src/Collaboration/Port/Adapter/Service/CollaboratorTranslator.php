<?php

namespace SaasOvation\Collaboration\Port\Adapter\Service;

use ReflectionClass;
use SaasOvation\Common\Media\RepresentationReader;

class CollaboratorTranslator
{
    public function toCollaboratorFromRepresentation($aUserInRoleRepresentation, $aCollaboratorClass)
    {
        $reader = RepresentationReader::fromString($aUserInRoleRepresentation);

        $username = $reader->stringValue('username');
        $firstName = $reader->stringValue('firstName');
        $lastName = $reader->stringValue('lastName');
        $emailAddress = $reader->stringValue('emailAddress');

        return $this->newCollaborator(
            $username,
            $firstName,
            $lastName,
            $emailAddress,
            $aCollaboratorClass
        );
    }

    private function newCollaborator(
        $aUsername,
        $aFirstName,
        $aLastName,
        $aEmailAddress,
        $aCollaboratorClass
    ) {
        $aReflectedCollaboratorClass = new ReflectionClass($aCollaboratorClass);

        return $aReflectedCollaboratorClass->newInstance(
            $aUsername,
            trim($aFirstName . ' ' . $aLastName),
            $aEmailAddress
        );
    }
}
