<?php

namespace SaasOvation\Collaboration\Test\Domain\Model\Forum;

use SaasOvation\Collaboration\Domain\Model\Forum\PostedToDiscussion;
use SaasOvation\Common\Domain\Model\DomainEvent;
use SaasOvation\Common\Domain\Model\DomainEventSubscriber;

class PostedToDiscussionSubscriber implements DomainEventSubscriber
{
    private $tenant;
    private $forumId;
    private $discussionId;
    private $postId;
    private $subject;
    private $bodyText;

    public function handleEvent(DomainEvent $aDomainEvent)
    {
        $this->tenant = $aDomainEvent->tenant();
        $this->forumId = $aDomainEvent->forumId();
        $this->discussionId = $aDomainEvent->discussionId();
        $this->postId = $aDomainEvent->postId();
        $this->subject = $aDomainEvent->subject();
        $this->bodyText = $aDomainEvent->bodyText();
    }

    public function subscribedToEventType()
    {
        return PostedToDiscussion::class;
    }

    public function getBodyText()
    {
        return $this->bodyText;
    }

    public function getDiscussionId()
    {
        return $this->discussionId;
    }

    public function getForumId()
    {
        return $this->forumId;
    }

    public function getPostId()
    {
        return $this->postId;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function getTenant()
    {
        return $this->tenant;
    }
}
