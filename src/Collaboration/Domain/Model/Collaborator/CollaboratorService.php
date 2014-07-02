<?php

namespace SaasOvation\Collaboration\Domain\Model\Collaborator;

use SaasOvation\Collaboration\Domain\Model\Tenant\Tenant;

interface CollaboratorService
{
    public function authorFrom(Tenant $aTenant, $anIdentity);

    public function creatorFrom(Tenant $aTenant, $anIdentity);

    public function moderatorFrom(Tenant $aTenant, $anIdentity);

    public function ownerFrom(Tenant $aTenant, $anIdentity);

    public function participantFrom(Tenant $aTenant, $anIdentity);
}
