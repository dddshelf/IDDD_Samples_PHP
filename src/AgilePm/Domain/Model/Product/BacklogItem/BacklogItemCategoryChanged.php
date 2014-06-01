<?php

namespace SaasOvation\AgilePm\Domain\Model\Product\BacklogItem;

use SaasOvation\AgilePm\Domain\Model\Discussion\DiscussionAvailability\Failed;
use SaasOvation\AgilePm\Domain\Model\Discussion\DiscussionAvailability\NotRequested;
use SaasOvation\AgilePm\Domain\Model\Entity;
use SaasOvation\Common\Domain\Model\DomainEvent;

class BacklogItemCategoryChanged implements DomainEvent
{
    /**
     * @var BacklogItemId
     */
    private $backlogItemId;

    /**
     * @var string
     */
    private $category;

    /**
     * @var int
     */
    private $eventVersion;

    /**
     * @var \DateTime
     */
    private $occurredOn;

    /**
     * @var TenantId
     */
    private $tenantId;

    /**
     * @param TenantId $aTenantId
     * @param BacklogItemId $aBacklogItemId
     * @param string $aCategory
     */
    public function __construct(TenantId $aTenantId, BacklogItemId $aBacklogItemId, $aCategory)
    {
        parent::__construct();

        $this->setBacklogItemId($aBacklogItemId);
        $this->setCategory($aCategory);
        $this->eventVersion = 1;
        $this->occurredOn = new \DateTime();
        $this->setTenantId($aTenantId);
    }

    /**
     * @return BacklogItemId
     */
    public function backlogItemId()
    {
        return $this->backlogItemId;
    }

    public function category()
    {
        return $this->category;
    }

    /**
     * @return int
     */
    public function eventVersion()
    {
        return $this->eventVersion;
    }

    /**
     * @return \DateTime
     */
    public function occurredOn()
    {
        return $this->occurredOn;
    }

    public function tenantId()
    {
        return $this->tenantId;
    }
}
