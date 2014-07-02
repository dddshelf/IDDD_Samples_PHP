<?php

namespace SaasOvation\Collaboration\Domain\Model\Forum;

use DateTimeImmutable;
use SaasOvation\Collaboration\Domain\Model\Collaborator\Creator;
use SaasOvation\Collaboration\Domain\Model\Collaborator\Moderator;
use SaasOvation\Collaboration\Domain\Model\Tenant\Tenant;
use SaasOvation\Common\Domain\Model\DomainEvent;
use SaasOvation\Common\Domain\Model\ImplementsDomainEvent;

class ForumStarted implements DomainEvent
{
    use ImplementsDomainEvent;

    /**
     * @var Creator
     */
    private $creator;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $exclusiveOwner;

    /**
     * @var ForumId
     */
    private $forumId;

    /**
     * @var Moderator
     */
    private $moderator;

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
        Creator $aCreator,
        Moderator $aModerator,
        $aSubject,
        $aDescription,
        $anExclusiveOwner
    ) {
        $this->creator = $aCreator;
        $this->description = $aDescription;
        $this->exclusiveOwner = $anExclusiveOwner;
        $this->forumId = $aForumId;
        $this->moderator = $aModerator;
        $this->occurredOn = new DateTimeImmutable();
        $this->subject = $aSubject;
        $this->tenant = $aTenant;
    }

    public function creator()
    {
        return $this->creator;
    }

    public function description()
    {
        return $this->description;
    }

    public function exclusiveOwner()
    {
        return $this->exclusiveOwner;
    }

    public function forumId()
    {
        return $this->forumId;
    }

    public function moderator()
    {
        return $this->moderator;
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
