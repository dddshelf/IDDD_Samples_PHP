<?php

namespace SaasOvation\Collaboration\Port\Adapter\Service;

use SaasOvation\Collaboration\Domain\Model\Collaborator\CollaboratorService;
use SaasOvation\Collaboration\Domain\Model\Tenant\Tenant;

class TranslatingCollaboratorService implements CollaboratorService
{
    /**
     * @var UserInRoleAdapter
     */
    private $userInRoleAdapter;

    public function __construct(UserInRoleAdapter $aUserInRoleAdapter)
    {
        $this->userInRoleAdapter = $aUserInRoleAdapter;
    }

    public function authorFrom(Tenant $aTenant, $anIdentity)
    {
        return $this->userInRoleAdapter()->toCollaborator(
            $aTenant,
            $anIdentity,
            'Author',
            '\SaasOvation\Collaboration\Domain\Model\Collaborator\Author'
        );
    }

    public function creatorFrom(Tenant $aTenant, $anIdentity)
    {
        return $this->userInRoleAdapter()->toCollaborator(
            $aTenant,
            $anIdentity,
            'Creator',
            '\SaasOvation\Collaboration\Domain\Model\Collaborator\Creator'
        );
    }

    public function moderatorFrom(Tenant $aTenant, $anIdentity)
    {
        return $this->userInRoleAdapter()->toCollaborator(
            $aTenant,
            $anIdentity,
            'Moderator',
            '\SaasOvation\Collaboration\Domain\Model\Collaborator\Moderator'
        );
    }

    public function ownerFrom(Tenant $aTenant, $anIdentity)
    {
        return $this->userInRoleAdapter()->toCollaborator(
            $aTenant,
            $anIdentity,
            'Owner',
            '\SaasOvation\Collaboration\Domain\Model\Collaborator\Owner'
        );
    }

    public function participantFrom(Tenant $aTenant, $anIdentity)
    {
        return $this->userInRoleAdapter()->toCollaborator(
            $aTenant,
            $anIdentity,
            'Participant',
            '\SaasOvation\Collaboration\Domain\Model\Collaborator\Participant'
        );
    }

    private function userInRoleAdapter()
    {
        return $this->userInRoleAdapter;
    }
}
