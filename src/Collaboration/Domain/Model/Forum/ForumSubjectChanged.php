<?php

namespace SaasOvation\Collaboration\Domain\Model\Forum;

use DateTimeImmutable;
use SaasOvation\Collaboration\Domain\Model\Tenant\Tenant;
use SaasOvation\Common\Domain\Model\DomainEvent;
use SaasOvation\Common\Domain\Model\ImplementsDomainEvent;

class ForumSubjectChanged implements DomainEvent
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
        $aSubject,
        $anExclusiveOwner
    ) {
        $this->exclusiveOwner = $anExclusiveOwner;
        $this->forumId = $aForumId;
        $this->occurredOn = new DateTimeImmutable();
        $this->subject = $aSubject;
        $this->tenant = $aTenant;
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
