<?php

namespace SaasOvation\Collaboration\Domain\Model\Forum;

use DateTimeImmutable;
use SaasOvation\Collaboration\Domain\Model\Tenant\Tenant;
use SaasOvation\Common\Domain\Model\DomainEvent;
use SaasOvation\Common\Domain\Model\ImplementsDomainEvent;

class ForumClosed implements DomainEvent
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
     * @var Tenant
     */
    private $tenant;

    public function __construct(Tenant $aTenant, ForumId $aForumId, $anExclusiveOwner)
    {
        $this->exclusiveOwner   = $anExclusiveOwner;
        $this->forumId          = $aForumId;
        $this->occurredOn       = new DateTimeImmutable();
        $this->tenant           = $aTenant;
    }

    public function exclusiveOwner()
    {
        return $this->exclusiveOwner;
    }

    public function forumId()
    {
        return $this->forumId;
    }

    public function tenant()
    {
        return $this->tenant;
    }
}
