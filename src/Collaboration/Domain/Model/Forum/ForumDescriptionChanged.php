<?php

namespace SaasOvation\Collaboration\Domain\Model\Forum;

use DateTimeImmutable;
use SaasOvation\Collaboration\Domain\Model\Tenant\Tenant;
use SaasOvation\Common\Domain\Model\DomainEvent;
use SaasOvation\Common\Domain\Model\ImplementsDomainEvent;

class ForumDescriptionChanged implements DomainEvent
{
    use ImplementsDomainEvent;

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
     * @var Tenant
     */
    private $tenant;

    public function __construct(
        Tenant $aTenant,
        ForumId $aForumId,
        $aDescription,
        $anExclusiveOwner
    ) {
        $this->description      = $aDescription;
        $this->exclusiveOwner   = $anExclusiveOwner;
        $this->forumId          = $aForumId;
        $this->occurredOn       = new DateTimeImmutable();
        $this->tenant           = $aTenant;
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

    public function tenant()
    {
        return $this->tenant;
    }
}
