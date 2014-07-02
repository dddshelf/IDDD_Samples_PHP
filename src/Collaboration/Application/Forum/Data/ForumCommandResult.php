<?php

namespace SaasOvation\Collaboration\Application\Forum\Data;

interface ForumCommandResult
{
    public function resultingForumId($aForumId);
    public function resultingDiscussionId($aDiscussionId);
}