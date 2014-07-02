<?php

namespace SaasOvation\Collaboration\Test\Application\Forum;

use SaasOvation\Collaboration\Application\Forum\Data\ForumCommandResult;

class CustomForumCommandResult implements ForumCommandResult
{
    private $forumId;
    private $discussionId;

    public function resultingForumId($aForumId)
    {
        $this->forumId = $aForumId;
    }

    public function resultingDiscussionId($aDiscussionId)
    {
        $this->discussionId = $aDiscussionId;
    }

    public function getForumId()
    {
        return $this->forumId;
    }

    public function getDiscussionId()
    {
        return $this->discussionId;
    }
}