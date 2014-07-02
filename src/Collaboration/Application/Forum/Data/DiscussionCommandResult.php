<?php

namespace SaasOvation\Collaboration\Application\Forum\Data;

interface DiscussionCommandResult
{
    public function resultingDiscussionId($aDiscussionId);
    public function resultingPostId($aPostId);
    public function resultingInReplyToPostId($aReplyToPostId);
}