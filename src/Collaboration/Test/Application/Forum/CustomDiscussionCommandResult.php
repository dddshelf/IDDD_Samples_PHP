<?php

namespace SaasOvation\Collaboration\Test\Application\Forum;

use SaasOvation\Collaboration\Application\Forum\Data\DiscussionCommandResult;

class CustomDiscussionCommandResult implements DiscussionCommandResult
{
    private $discussionId;
    private $postId;
    private $inReplyToPostId;

    public function resultingDiscussionId($aDiscussionId)
    {
        $this->discussionId = $aDiscussionId;
    }

    public function resultingPostId($aPostId)
    {
        $this->postId = $aPostId;
    }

    public function resultingInReplyToPostId($aReplyToPostId)
    {
        $this->inReplyToPostId = $aReplyToPostId;
    }

    public function getDiscussionId()
    {
        return $this->discussionId;
    }

    public function getPostId()
    {
        return $this->postId;
    }

    public function getInReplyToPostId()
    {
        return $this->inReplyToPostId;
    }
}
