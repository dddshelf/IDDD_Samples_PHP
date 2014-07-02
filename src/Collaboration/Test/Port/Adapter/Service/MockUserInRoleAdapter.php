<?php

namespace SaasOvation\Collaboration\Test\Port\Adapter\Service;

use Exception;
use SaasOvation\Collaboration\Domain\Model\Tenant\Tenant;
use SaasOvation\Collaboration\Port\Adapter\Service\CollaboratorTranslator;
use SaasOvation\Collaboration\Port\Adapter\Service\UserInRoleAdapter;

class MockUserInRoleAdapter implements UserInRoleAdapter
{
    private static $USER_IN_ROLE_REPRESENTATION = '{
        "role": "UNUSED",
        "username": "$uid$",
        "tenantId": "$tid$",
        "firstName": "First",
        "lastName": "Last",
        "emailAddress": "$uid$@saasovation.com"
    }';

    public function toCollaborator(
        Tenant $aTenant,
        $anIdentity,
        $aRoleName,
        $aCollaboratorClass
    ) {

        $representation = str_replace('$uid$', $anIdentity, static::$USER_IN_ROLE_REPRESENTATION);
        $representation = str_replace('$tid$', $aTenant->id(), $representation);

        $collaborator = null;

        try {
            $collaborator = (new CollaboratorTranslator())->toCollaboratorFromRepresentation(
                $representation,
                $aCollaboratorClass
            );
        } catch (Exception $e) {
            throw new \InvalidArgumentException('Cannot create collaborator.');
        }

        return $collaborator;
    }
}
