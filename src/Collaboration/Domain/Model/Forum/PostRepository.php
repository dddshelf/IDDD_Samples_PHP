<?php

namespace SaasOvation\Collaboration\Domain\Model\Forum;

use SaasOvation\Collaboration\Domain\Model\Tenant\Tenant;

interface PostRepository
{
    /**
     * @return PostId
     */
    public function nextIdentity();

    /**
     * @param Tenant $aTenant
     * @param PostId $aPostId
     *
     * @return Post
     */
    public function postOfId(Tenant $aTenant, PostId $aPostId);

    public function save(Post $aPost);
}
