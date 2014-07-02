<?php

namespace SaasOvation\Collaboration\Domain\Model\Forum;

use DateTimeImmutable;
use SaasOvation\Collaboration\Domain\Model\Tenant\Tenant;
use SaasOvation\Common\Domain\Model\DomainEvent;
use SaasOvation\Common\Domain\Model\ImplementsDomainEvent;

class PostContentAltered implements DomainEvent
{
    use ImplementsDomainEvent;

    /**
     * @var string
     */
    private $bodyText;

    /**
     * @var DiscussionId
     */
    private $discussionId;

    /**
     * @var ForumId
     */
    private $forumId;

    /**
     * @var PostId
     */
    private $postId;

    /**
     * @var string
     */
    private $subject;

    /**
     * @var Tenant
     */
    private $tenant;

    public function __construct(
        Tenant $aTenant,
        ForumId $aForumId,
        DiscussionId $aDiscussionId,
        PostId $aPostId,
        $aSubject,
        $aBodyText
    ) {
        $this->bodyText = $aBodyText;
        $this->discussionId = $aDiscussionId;
        $this->forumId = $aForumId;
        $this->occurredOn = new DateTimeImmutable();
        $this->postId = $aPostId;
        $this->subject = $aSubject;
        $this->tenant = $aTenant;
    }

    public function bodyText()
    {
        return $this->bodyText;
    }

    public function discussionId()
    {
        return $this->discussionId;
    }

    public function forumId()
    {
        return $this->forumId;
    }

    public function postId()
    {
        return $this->postId;
    }

    public function subject()
    {
        return $this->subject;
    }

    public function tenant()
    {
        return $this->tenant;
    }
}
