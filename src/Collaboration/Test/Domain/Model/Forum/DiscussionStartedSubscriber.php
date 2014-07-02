<?php

namespace SaasOvation\Collaboration\Test\Domain\Model\Forum;

use SaasOvation\Collaboration\Domain\Model\Forum\DiscussionStarted;
use SaasOvation\Common\Domain\Model\DomainEvent;
use SaasOvation\Common\Domain\Model\DomainEventSubscriber;

class DiscussionStartedSubscriber implements DomainEventSubscriber
{
    private $discussionId;
    private $tenant;
    private $forumId;
    private $subject;

    public function handleEvent(DomainEvent $aDomainEvent)
    {
        $this->discussionId     = $aDomainEvent->discussionId();
        $this->tenant           = $aDomainEvent->tenant();
        $this->forumId          = $aDomainEvent->forumId();
        $this->subject          = $aDomainEvent->subject();
    }

    public function subscribedToEventType()
    {
        return DiscussionStarted::class;
    }

    public function getDiscussionId()
    {
        return $this->discussionId;
    }

    public function getForumId()
    {
        return $this->forumId;
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
