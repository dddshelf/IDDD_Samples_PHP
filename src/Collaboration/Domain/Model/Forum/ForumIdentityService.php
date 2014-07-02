<?php

namespace SaasOvation\Collaboration\Domain\Model\Forum;

class ForumIdentityService
{
    /**
     * @var DiscussionRepository
     */
    private $discussionRepository;

    /**
     * @var ForumRepository
     */
    private $forumRepository;

    /**
     * @var PostRepository
     */
    private $postRepository;

    public function __construct(
        ForumRepository $aForumRepository,
        DiscussionRepository $aDiscussionRepository,
        PostRepository $aPostRepository
    ) {
        $this->discussionRepository     = $aDiscussionRepository;
        $this->forumRepository          = $aForumRepository;
        $this->postRepository           = $aPostRepository;
    }

    public function nextDiscussionId()
    {
        return $this->discussionRepository()->nextIdentity();
    }

    public function nextForumId()
    {
        return $this->forumRepository()->nextIdentity();
    }

    public function nextPostId()
    {
        return $this->postRepository()->nextIdentity();
    }

    private function discussionRepository()
    {
        return $this->discussionRepository;
    }

    private function forumRepository()
    {
        return $this->forumRepository;
    }

    private function postRepository()
    {
        return $this->postRepository;
    }
}
