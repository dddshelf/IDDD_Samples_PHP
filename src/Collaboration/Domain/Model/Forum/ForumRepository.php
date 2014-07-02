<?php

namespace SaasOvation\Collaboration\Domain\Model\Forum;

use SaasOvation\Collaboration\Domain\Model\Tenant\Tenant;

interface ForumRepository
{
    /**
     * @param Tenant $aTenant
     * @param ForumId $aForumId
     *
     * @return Forum
     */
    public function forumOfId(Tenant $aTenant, ForumId $aForumId);

    /**
     * @return ForumId
     */
    public function nextIdentity();

    public function save(Forum $aForum);
}
