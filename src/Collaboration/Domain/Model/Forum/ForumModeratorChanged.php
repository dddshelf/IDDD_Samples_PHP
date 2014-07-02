<?php

namespace SaasOvation\Collaboration\Domain\Model\Forum;

use DateTimeImmutable;
use SaasOvation\Collaboration\Domain\Model\Collaborator\Moderator;
use SaasOvation\Collaboration\Domain\Model\Tenant\Tenant;
use SaasOvation\Common\Domain\Model\DomainEvent;
use SaasOvation\Common\Domain\Model\ImplementsDomainEvent;

class ForumModeratorChanged implements DomainEvent
{
    use ImplementsDomainEvent;

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
     * @var Tenant
     */
    private $tenant;

    public function __construct(
        Tenant $aTenant,
        ForumId $aForumId,
        Moderator $aModerator,
        $anExclusiveOwner
    ) {
        $this->exclusiveOwner = $anExclusiveOwner;
        $this->forumId = $aForumId;
        $this->moderator = $aModerator;
        $this->tenant = $aTenant;
        $this->occurredOn = new DateTimeImmutable();
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

    public function tenant()
    {
        return $this->tenant;
    }
}
