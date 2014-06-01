<?php

namespace SaasOvation\AgilePm\Domain\Model\Product\BacklogItem;

use SaasOvation\AgilePm\Domain\Model\Discussion\DiscussionAvailability\Failed;
use SaasOvation\AgilePm\Domain\Model\Discussion\DiscussionAvailability\NotRequested;
use SaasOvation\AgilePm\Domain\Model\Entity;

class BacklogItem extends Entity
{
    /**
     * @var string
     */
    private $associatedIssueId;

    /**
     * @var BacklogItemId
     */
    private $backlogItemId;

    /**
     * @var BusinessPriority
     */
    private $businessPriority;

    /**
     * @var string
     */
    private $category;

    /**
     * @var BacklogItemDiscussion
     */
    private $discussion;

    /**
     * @var string
     */
    private $discussionInitiationId;

    /**
     * @var ProductId
     */
    private $productId;

    /**
     * @var ReleaseId
     */
    private $releaseId;

    /**
     * @var SprintId
     */
    private $sprintId;

    /**
     * @var BacklogItemStatus
     */
    private $status;

    /**
     * @var string
     */
    private $story;

    /**
     * @var StoryPoints
     */
    private $storyPoints;

    /**
     * @var string
     */
    private $summary;

    /**
     * @var Task[]
     */
    private $tasks;

    /**
     * @var TenantId
     */
    private $tenantId;

    /**
     * @var BacklogItemType
     */
    private $type;

    public function __construct(
        TenantId $aTenantId,
        ProductId $aProductId,
        BacklogItemId $aBacklogItemId,
        $aSummary,
        $aCategory,
        BacklogItemType $aType,
        BacklogItemStatus $aStatus,
        StoryPoints $aStoryPoints) {

        parent::__construct();
        $this->setBacklogItemId($aBacklogItemId);
        $this->setCategory($aCategory);
        $this->setDiscussion(BacklogItemDiscussion::fromAvailability(new NotRequested()));
        $this->setProductId($aProductId);
        $this->setStatus($aStatus);
        $this->setStoryPoints($aStoryPoints);
        $this->setSummary($aSummary);
        $this->setTenantId($aTenantId);
        $this->setType($aType);

    }

    /**
     * @todo Use unmodifiable set
     * @return Task[]
     */
    public function allTasks()
    {
        return $this->tasks();
    }

    /**
     * @return bool
     */
    public function anyTaskHoursRemaining()
    {
        return $this->totalTaskHoursRemaining() > 0;
    }

    /**
     * @return string
     */
    public function associatedIssueId()
    {
        return $this->associatedIssueId;
    }

    /**
     * @param string $anIssueId
     */
    public function associateWithIssue($anIssueId) {
        if (null === $this->associatedIssueId) {
            $this->associatedIssueId = $anIssueId;
        }
    }

    public function assignBusinessPriority(BusinessPriority $aBusinessPriority) {
        $this->setBusinessPriority($aBusinessPriority);

        DomainEventPublisher::instance()->publish(
            new BusinessPriorityAssigned(
                $this->tenantId(),
                $this->backlogItemId(),
                $this->businessPriority()
            )
        );
    }

    public function assignStoryPoints(StoryPoints $aStoryPoints) {
        $this->setStoryPoints($aStoryPoints);

        DomainEventPublisher::instance()->publish(
            new BacklogItemStoryPointsAssigned(
                $this->tenantId(),
                $this->backlogItemId(),
                $this->storyPoints()
            )
        );
    }

    public function assignTaskVolunteer(TaskId $aTaskId, TeamMember $aVolunteer) {
        $task = $this->task($aTaskId);

        if (null === $task) {
            throw new \IllegalStateException("Task does not exist.");
        }

        $task->assignVolunteer($aVolunteer);
    }

    /**
     * @return BacklogItemId
     */
    public function backlogItemId() {
        return $this->backlogItemId;
    }

    /**
     * @return BusinessPriority
     */
    public function businessPriority() {
        return $this->businessPriority;
    }

    /**
     * @return string
     */
    public function category() {
        return $this->category;
    }

    /**
     * @param string $aCategory
     */
    public function changeCategory($aCategory) {
        $this->setCategory($aCategory);

        DomainEventPublisher
        ::instance()
        ->publish(
            new BacklogItemCategoryChanged(
                $this->tenantId(),
                $this->backlogItemId(),
                $this->category()));
    }

    public function changeTaskStatus(TaskId $aTaskId, TaskStatus $aStatus) {
        $task = $this->task($aTaskId);
        if (null === $task) {
            throw new \IllegalStateException("Task does not exist.");
        }

        $task->changeStatus($aStatus);
    }

    public function changeType(BacklogItemType $aType) {
        $this->setType($aType);

        DomainEventPublisher
        ::instance()
        ->publish(
            new BacklogItemTypeChanged(
                $this->tenantId(),
                $this->backlogItemId(),
                $this->type()));
    }

    /**
     * @param $aSprint
     * @throws IllegalStateException
     */
    public function commitTo($aSprint) {
        $this->assertArgumentNotNull($aSprint, "Sprint must not be null.");
        $this->assertArgumentEquals($aSprint->tenantId(), $this->tenantId(), "Sprint must be of same tenant.");
        $this->assertArgumentEquals($aSprint->productId(), $this->productId(), "Sprint must be of same product.");

        if (!$this->isScheduledForRelease()) {
            throw new \IllegalStateException("Must be scheduled for release to commit to sprint.");
        }

        if ($this->isCommittedToSprint()) {
            if (!$aSprint->sprintId() === $this->sprintId()) {
                $this->uncommitFromSprint();
            }
        }

        /** @todo BacklogItemStatus::COMMITTED */
        $this->elevateStatusWith(new Commited());

        $this->setSprintId($aSprint->sprintId());

        DomainEventPublisher::instance()->publish(new BacklogItemCommitted(
                $this->tenantId(),
                $this->backlogItemId(),
                $this->sprintId()));
    }

    public function defineTask(TeamMember $aVolunteer, $aName, $aDescription, $anHoursRemaining) {
        $task = new Task(
            $this->tenantId(),
            $this->backlogItemId(),
            new TaskId(),
            $aVolunteer,
            $aName,
            $aDescription,
            $anHoursRemaining,
            TaskStatus.NOT_STARTED
        );

        $this->tasks().add(task);

        DomainEventPublisher
        ::instance()
        ->publish(new TaskDefined(
                $this->tenantId(),
                $this->backlogItemId(),
                $task->taskId(),
                $aVolunteer->username(),
                $aName,
                $aDescription,
                $anHoursRemaining));
    }

    public function describeTask(TaskId $aTaskId, $aDescription) {
        $task = $this->task($aTaskId);
        if (null === $task) {
            throw new \IllegalStateException("Task does not exist.");
        }

        $task->describeAs($aDescription);
    }

    /**
     * @return BacklogItemDiscussion
     */
    public function discussion() {
        return $this->discussion;
    }

    /**
     * @return string
     */
    public function discussionInitiationId() {
        return $this->discussionInitiationId;
    }

    public function failDiscussionInitiation() {
        if (!$this->discussion()->availability()->isReady()) {
            $this->setDiscussionInitiationId(null);
            $this->setDiscussion(
                BacklogItemDiscussion::fromAvailability(new Failed())
            );
        }
    }

    public function initiateDiscussion(DiscussionDescriptor $aDescriptor) {
        if ($aDescriptor == null) {
            throw new IllegalArgumentException("The descriptor must not be null.");
        }

        if ($this->discussion()->availability()->isRequested()) {
            $this->setDiscussion($this->discussion()->nowReady($aDescriptor));

            DomainEventPublisher
            ::instance()
            ->publish(
                new BacklogItemDiscussionInitiated(
                    $this->tenantId(),
                    $this->backlogItemId(),
                    $this->discussion()));
        }
    }

    /**
     * @param TaskId $aTaskId
     * @param int $anHoursRemaining
     * @throws \IllegalStateException
     */
    public function estimateTaskHoursRemaining(TaskId $aTaskId, $anHoursRemaining) {
        $task = $this->task($aTaskId);

        if (null === $task) {
            throw new \IllegalStateException("Task does not exist.");
        }

        $task->estimateHoursRemaining($anHoursRemaining);

        $changedStatus = null;
        if ($anHoursRemaining == 0) {
            if (!$this->anyTaskHoursRemaining()) {
                // @todo $changedStatus = BacklogItemStatus.DONE;
                $changedStatus = new BacklogItemStatus\Done();
            }
        } elseif ($this->isDone()) {
            // regress to the logical previous state
            // because "done" is no longer appropriate
            if ($this->isCommittedToSprint()) {
                $changedStatus = new BacklogItemStatus\Committed();
            } elseif ($this->isScheduledForRelease()) {
                $changedStatus = new BacklogItemStatus\Scheduled();
            } else {
                $changedStatus = new BacklogItemStatus\Planned();
            }
        }

        if ($changedStatus !== null) {
            $this->setStatus($changedStatus);

            DomainEventPublisher
            ::instance()
            ->publish(
                new BacklogItemStatusChanged(
                    $this->tenantId(),
                    $this->backlogItemId(),
                    changedStatus));
        }
    }

    /**
     * @return bool
     */
    public function hasBusinessPriority() {
        return $this->businessPriority() !== null;
    }

    public function initiateDiscussionFromBacklogItemDiscussion(BacklogItemDiscussion $aDiscussion) {
        $this->setDiscussion(aDiscussion);

        DomainEventPublisher
        ::instance()
        ->publish(new BacklogItemDiscussionInitiated(
                $this->tenantId(),
                $this->backlogItemId(),
                $this->discussion()));
    }

    /**
     * @return bool
     */
    public function isCommittedToSprint() {
        return $this->sprintId() !== null;
    }

    /**
     * @return bool
     */
    public function isDone() {
        return $this->status()->isDone();
    }

    /**
     * @return bool
     */
    public function isPlanned() {
        return $this->status()->isPlanned();
    }

    /**
     * @return bool
     */
    public function isRemoved() {
        return $this->status()->isRemoved();
    }

    /**
     * @return bool
     */
    public function isScheduledForRelease() {
        return $this->releaseId() !== null;
    }

    public function markAsRemoved() {
        if ($this->isRemoved()) {
            throw new IllegalStateException("Already removed, not outstanding.");
        }
        if ($this->isDone()) {
            throw new IllegalStateException("Already done, not outstanding.");
        }
        if ($this->isCommittedToSprint()) {
            $this->uncommitFromSprint();
        }
        if ($this->isScheduledForRelease()) {
            $this->unscheduleFromRelease();
        }

        // @todo BacklogItemStatus.REMOVED
        $this->setStatus(new BacklogItemStatus\Removed());

        DomainEventPublisher
        ::instance()
        ->publish(new BacklogItemMarkedAsRemoved(
                $this->tenantId(),
                $this->backlogItemId()));
    }

    /**
     * @return ProductId
     */
    public function productId() {
        return $this->productId;
    }

    /**
     * @return ReleaseId
     */
    public function releaseId() {
        return $this->releaseId;
    }

    public function removeTask(TaskId $aTaskId) {
        $task = $this->task($aTaskId);

        if ($task === null) {
            throw new \IllegalStateException("Task does not exist.");
        }

        if (!$this->tasks()->remove($task)) {
            throw new \IllegalStateException("Task was not removed.");
        }

        DomainEventPublisher
        ::instance()
        ->publish(
            new TaskRemoved(
                $this->tenantId(),
                $this->backlogItemId(),
                aTaskId));
    }

    /**
     * @param TaskId $aTaskId
     * @param string $aName
     * @throws \IllegalStateException
     */
    public function renameTask(TaskId $aTaskId, $aName) {
        $task = $this->task($aTaskId);
        if (null === $task) {
            throw new \IllegalStateException("Task does not exist.");
        }

        $task.rename($aName);
    }

    public function requestDiscussion(DiscussionAvailability $aDiscussionAvailability) {
        if (!$this->discussion()->availability()->isReady()) {
            $this->setDiscussion(
                BacklogItemDiscussion::fromAvailability(
                    $aDiscussionAvailability));

            DomainEventPublisher
            ::instance()
            ->publish(new BacklogItemDiscussionRequested(
                    $this->tenantId(),
                    $this->productId(),
                    $this->backlogItemId(),
                    $this->discussion()->availability()->isRequested()));
        }
    }

    public function scheduleFor(Release $aRelease) {
        $this->assertArgumentNotNull($aRelease, "Release must not be null.");
        $this->assertArgumentEquals($aRelease->tenantId(), $this->tenantId(), "Release must be of same tenant.");
        $this->assertArgumentEquals($aRelease->productId(), $this->productId(), "Release must be of same product.");

        if ($this->isScheduledForRelease()) {
            if (!$aRelease->releaseId() === $this->releaseId()) {
                $this->unscheduleFromRelease();
            }
        }

        if ($this->status()->isPlanned()) {
            $this->setStatus(new \BacklogItemStatus\Scheduled);
        }

        $this->setReleaseId($aRelease->releaseId());

        DomainEventPublisher
        ::instance()
        ->publish(
            new BacklogItemScheduled(
                $this->tenantId(),
                $this->backlogItemId(),
                $this->releaseId()));
    }

    /**
     * @return SprintId
     */
    public function sprintId() {
        return $this->sprintId;
    }

    public function startDiscussionInitiation($aDiscussionInitiationId) {
        if (!$this->discussion()->availability()->isReady()) {
            $this->setDiscussionInitiationId($aDiscussionInitiationId);
        }
    }

    /**
     * @return String
     */
    public function story() {
        return $this->story;
    }

    /**
     * @return StoryPoints
     */
    public function storyPoints() {
        return $this->storyPoints;
    }

    /**
     * @return string
     */
    public function summary() {
        return $this->summary;
    }

    public function summarize($aSummary) {
        $this->setSummary($aSummary);

        DomainEventPublisher
        ::instance()
        ->publish(new BacklogItemSummarized(
                $this->tenantId(),
                $this->backlogItemId(),
                $this->summary()));
    }

    /**
     * @param TaskId $aTaskId
     * @return null|Task
     */
    public function task(TaskId $aTaskId) {
        foreach ($this->tasks() as $task) {
            if ($task->taskId() === $aTaskId) {
                return $task;
            }
        }

        return null;
    }

    /**
     * @param string $aStory
     */
    public function tellStory($aStory) {
        $this->setStory($aStory);

        DomainEventPublisher
        ::instance()
        ->publish(
            new BacklogItemStoryTold(
                $this->tenantId(),
                $this->backlogItemId(),
                $this->story()));
    }

    /**
     * @return TenantId
     */
    public function tenantId() {
        return $this->tenantId;
    }

    /**
     * @return int
     */
    public function totalTaskHoursRemaining() {
        $totalHoursRemaining = 0;

        foreach ($this->tasks() as $task) {
            $totalHoursRemaining += $task->hoursRemaining();
        }

        return $totalHoursRemaining;
    }

    /**
     * @return BacklogItemType
     */
    public function type() {
        return $this->type;
    }

    public function uncommitFromSprint() {
        if (!$this->isCommittedToSprint()) {
            throw new \IllegalStateException("Not currently committed.");
        }

        $this->setStatus(new \BacklogItemStatus\Scheduled);
        $uncommittedSprintId = $this->sprintId();
        $this->setSprintId(null);

        DomainEventPublisher
        ::instance()
        ->publish(
            new BacklogItemUncommitted(
                $this->tenantId(),
                $this->backlogItemId(),
                $uncommittedSprintId));
    }

    public function unscheduleFromRelease() {
        if ($this->isCommittedToSprint()) {
            throw new \IllegalStateException("Must first uncommit.");
        }
        if (!$this->isScheduledForRelease()) {
            throw new \IllegalStateException("Not scheduled for release.");
        }

        $this->setStatus(new \BacklogItemStatus\Planned());
        $unscheduledReleaseId = $this->releaseId();
        $this->setReleaseId(null);

        DomainEventPublisher
        ::instance()
        ->publish(
            new BacklogItemUnscheduled(
                $this->tenantId(),
                $this->backlogItemId(),
                $unscheduledReleaseId)
        );
    }

    public function equals($anObject) {
        $equalObjects = false;
        if (anObject != null && $this->getClass() == anObject.getClass()) {
            BacklogItem typedObject = (BacklogItem) anObject;
            equalObjects =
                $this->tenantId().equals(typedObject.tenantId()) &&
                $this->productId().equals(typedObject.productId()) &&
                $this->backlogItemId().equals(typedObject.backlogItemId());
        }

        return equalObjects;
    }

    public function __toString() {
        return "BacklogItem [tenantId=".$this->tenantId.", productId=".$this->productId
       .", backlogItemId=".$this->backlogItemId
       .", businessPriority=".$this->businessPriority
       .", category=".$this->category.", discussion=".$this->discussion
       .", releaseId=".$this->releaseId.", sprintId=".$this->sprintId
       .", status=".$this->status.", story=".$this->story
       .", storyPoints=".$this->storyPoints.", summary=".$this->summary
       .", tasks=".$this->tasks.", type=".$this->type."]";
    }

    private function BacklogItem() {
        super();

        $this->setTasks(new HashSet<Task>(0));
    }

    private function setBacklogItemId(BacklogItemId $aBacklogItemId) {
        $this->assertArgumentNotNull($aBacklogItemId, "The backlogItemId must be provided.");

        $this->backlogItemId = $aBacklogItemId;
    }

    private function setBusinessPriority(BusinessPriority $aBusinessPriority) {
        $this->businessPriority = $aBusinessPriority;
    }

    /**
     * @param string $aCategory
     * @throws \IllegalArgumentException
     */
    private function setCategory($aCategory) {
        $this->assertArgumentNotEmpty($aCategory, "The category must be provided.");
        $this->assertArgumentLength($aCategory, 25, "The category must be 25 characters or less.");

        $this->category = $aCategory;
    }

    /**
     * @param BacklogItemDiscussion $aDiscussion
     */
    private function setDiscussion(BacklogItemDiscussion $aDiscussion) {
        $this->discussion = $aDiscussion;
    }

    /**
     * @param string $aDiscussionInitiationId
     * @throws \IllegalArgumentException
     */
    private function setDiscussionInitiationId($aDiscussionInitiationId) {
        if (null !== $aDiscussionInitiationId) {
            $this->assertArgumentLength($aDiscussionInitiationId, 100, "Discussion initiation identity must be 100 characters or less.");
        }

        $this->discussionInitiationId = $aDiscussionInitiationId;
    }

    private function setProductId(ProductId $aProductId) {
        $this->assertArgumentNotNull($aProductId, "The product id must be provided.");

        $this->productId = $aProductId;
    }

    private function setReleaseId(ReleaseId $aReleaseId) {
        $this->releaseId = $aReleaseId;
    }

    private function setSprintId(SprintId $aSprintId) {
        $this->sprintId = $aSprintId;
    }

    private function status() {
        return $this->status;
    }

    private function elevateStatusWith(BacklogItemStatus $aStatus) {
        if ($this->status()->isScheduled()) {
            $this->setStatus(new BacklogItemStatus\Committed);
        }
    }

    private function setStatus(BacklogItemStatus $aStatus) {
        $this->status = $aStatus;
    }

    private function setStory(String $aStory) {
        if ($aStory != null) {
            $this->assertArgumentLength($aStory, 65000, "The story must be 65000 characters or less.");
        }

        $this->story = $aStory;
    }

    private function setStoryPoints(StoryPoints $aStoryPoints) {
        $this->storyPoints = $aStoryPoints;
    }

    private function setSummary($aSummary) {
        $this->assertArgumentNotEmpty($aSummary, "The summary must be provided.");
        $this->assertArgumentLength($aSummary, 100, "The summary must be 100 characters or less.");

        $this->summary = $aSummary;
    }

    private function tasks() {
        return $this->tasks;
    }

    /**
     * @param Task[] $aTasks
     */
    private function setTasks($aTasks) {
        $this->tasks = $aTasks;
    }

    private function setTenantId(TenantId $aTenantId) {
        $this->assertArgumentNotNull($aTenantId, "The tenant id must be provided.");
        $this->tenantId = $aTenantId;
    }

    private function setType(BacklogItemType $aType) {
        $this->assertArgumentNotNull($aType, "The backlog item type must be provided.");
        $this->type = $aType;
    }
}
