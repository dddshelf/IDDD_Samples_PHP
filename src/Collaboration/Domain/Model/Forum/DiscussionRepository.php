<?php

namespace SaasOvation\Collaboration\Domain\Model\Forum;

use SaasOvation\Collaboration\Domain\Model\Tenant\Tenant;

interface DiscussionRepository
{
    /**
     * @param Tenant $aTenantId
     * @param DiscussionId $aDiscussionId
     * 
     * @return Discussion
     */
    public function discussionOfId(Tenant $aTenantId, DiscussionId $aDiscussionId);

    public function nextIdentity();

    public function save(Discussion $aDiscussion);
}
