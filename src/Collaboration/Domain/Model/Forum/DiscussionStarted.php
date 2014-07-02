<?php

namespace SaasOvation\Collaboration\Domain\Model\Forum;

use DateTimeImmutable;
use DateTimeInterface;
use SaasOvation\Collaboration\Domain\Model\Collaborator\Author;
use SaasOvation\Collaboration\Domain\Model\Tenant\Tenant;
use SaasOvation\Common\Domain\Model\DomainEvent;
use SaasOvation\Common\Domain\Model\ImplementsDomainEvent;

class DiscussionStarted implements DomainEvent
{
    use ImplementsDomainEvent;

    /**
     * @var Author
     */
    private $author;

    /**
     * @var DiscussionId
     */
    private $discussionId;

    /**
     * @var string
     */
    private $exclusiveOwner;

    /**
     * @var ForumId
     */
    private $forumId;

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
        Author $anAuthor,
        $aSubject,
        $anExclusiveOwner
    ) {
        $this->author = $anAuthor;
        $this->discussionId = $aDiscussionId;
        $this->exclusiveOwner = $anExclusiveOwner;
        $this->forumId = $aForumId;
        $this->occurredOn = new DateTimeImmutable();
        $this->subject = $aSubject;
        $this->tenant = $aTenant;
    }

    public function author()
    {
        return $this->author;
    }

    public function discussionId()
    {
        return $this->discussionId;
    }

    public function exclusiveOwner()
    {
        return $this->exclusiveOwner;
    }

    public function forumId()
    {
        return $this->forumId;
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
