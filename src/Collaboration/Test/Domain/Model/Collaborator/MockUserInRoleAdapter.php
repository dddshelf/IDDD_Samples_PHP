<?php

namespace SaasOvation\Collaboration\Test\Domain\Model\Collaborator;

use Exception;
use RuntimeException;
use SaasOvation\Collaboration\Domain\Model\Tenant\Tenant;
use SaasOvation\Collaboration\Port\Adapter\Service\CollaboratorTranslator;
use SaasOvation\Collaboration\Port\Adapter\Service\UserInRoleAdapter;
use SaasOvation\Collaboration\Test\Domain\Model\Collaborator\CollaboratorServiceTest;

class MockUserInRoleAdapter implements UserInRoleAdapter
{
    public function toCollaborator(
        Tenant $aTenant,
        $anIdentity,
        $aRoleName,
        $aCollaboratorClass
    ) {
        $collaborator = null;

        // only eliminates the HTTP client;
        // still uses translator

        try {
            $collaborator = (new CollaboratorTranslator())->toCollaboratorFromRepresentation(
                CollaboratorServiceTest::$USER_IN_ROLE_REPRESENTATION,
                $aCollaboratorClass
            );
        } catch (Exception $e) {
            throw new RuntimeException('Cannot adapt ' . $aRoleName, $e->getCode(), $e);
        }

        return $collaborator;
    }
}