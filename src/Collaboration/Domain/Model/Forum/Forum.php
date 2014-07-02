<?php

namespace SaasOvation\Collaboration\Domain\Model\Forum;

use SaasOvation\Collaboration\Domain\Model\Collaborator\Author;
use SaasOvation\Collaboration\Domain\Model\Collaborator\Creator;
use SaasOvation\Collaboration\Domain\Model\Collaborator\Moderator;
use SaasOvation\Collaboration\Domain\Model\Tenant\Tenant;
use SaasOvation\Common\Domain\Model\DomainEvent;
use SaasOvation\Common\Domain\Model\EventSourcedRootEntity;
use Symfony\Component\DependencyInjection\Exception\BadMethodCallException;

class Forum extends EventSourcedRootEntity
{
    /**
     * @var bool
     */
    private $closed;

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

    private function createFrom(
        Tenant $aTenant,
        ForumId $aForumId,
        Creator $aCreator,
        Moderator $aModerator,
        $aSubject,
        $aDescription,
        $anExclusiveOwner
    ) {
        $this->assertArgumentNotNull($aCreator, 'The creator must be provided.');
        $this->assertArgumentNotEmpty($aDescription, 'The description must be provided.');
        $this->assertArgumentNotNull($aForumId, 'The forum id must be provided.');
        $this->assertArgumentNotNull($aModerator, 'The moderator must be provided.');
        $this->assertArgumentNotEmpty($aSubject, 'The subject must be provided.');
        $this->assertArgumentNotNull($aTenant, 'The creator must be provided.');
    
        $this->apply(
            new ForumStarted($aTenant, $aForumId, $aCreator, $aModerator, $aSubject, $aDescription, $anExclusiveOwner)
        );
    }

    public static function create(
        Tenant $aTenant,
        ForumId $aForumId,
        Creator $aCreator,
        Moderator $aModerator,
        $aSubject,
        $aDescription,
        $anExclusiveOwner
    ) {
        $aForum = new Forum();

        $aForum->createFrom(
            $aTenant,
            $aForumId,
            $aCreator,
            $aModerator,
            $aSubject,
            $aDescription,
            $anExclusiveOwner
        );

        return $aForum;
    }

    public function assignModerator(Moderator $aModerator)
    {
        $this->assertStateFalse($this->isClosed(), 'Forum is closed.');
        $this->assertArgumentNotNull($aModerator, 'The moderator must be provided.');

        $this->apply(
            new ForumModeratorChanged($this->tenant(), $this->forumId(), $aModerator, $this->exclusiveOwner())
        );
    }

    public function changeDescription($aDescription)
    {
        $this->assertStateFalse($this->isClosed(), 'Forum is closed.');
        $this->assertArgumentNotEmpty($aDescription, 'The description must be provided.');

        $this->apply(
            new ForumDescriptionChanged($this->tenant(), $this->forumId(), $aDescription, $this->exclusiveOwner())
        );
    }

    public function changeSubject($aSubject)
    {
        $this->assertStateFalse($this->isClosed(), 'Forum is closed.');
        $this->assertArgumentNotEmpty($aSubject, 'The subject must be provided.');

        $this->apply(
            new ForumSubjectChanged($this->tenant(), $this->forumId(), $aSubject, $this->exclusiveOwner())
        );
    }

    public function close()
    {
        $this->assertStateFalse($this->isClosed(), 'Forum is already closed.');

        $this->apply(
            new ForumClosed($this->tenant(), $this->forumId(), $this->exclusiveOwner())
        );
    }

    public function isClosed()
    {
        return $this->closed;
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

    public function hasExclusiveOwner()
    {
        return null !== $this->exclusiveOwner();
    }

    public function forumId()
    {
        return $this->forumId;
    }

    public function isModeratedBy(Moderator $aModerator)
    {
        return $this->moderator()->equals($aModerator);
    }

    public function moderatePost(
        Post $aPost,
        Moderator $aModerator,
        $aSubject,
        $aBodyText
    ) {
        $this->assertStateFalse($this->isClosed(), 'Forum is closed.');
        $this->assertArgumentNotNull($aPost, 'Post may not be null.');
        $this->assertArgumentEquals($aPost->forumId(), $this->forumId(), 'Not a post of $this forum.');
        $this->assertArgumentTrue($this->isModeratedBy($aModerator), 'Not the moderator of this forum.');

        $aPost->alterPostContent($aSubject, $aBodyText);
    }

    public function moderator()
    {
        return $this->moderator;
    }

    public function reopen()
    {
        $this->assertStateTrue($this->isClosed(), 'Forum is not closed.');

        $this->apply(
            new ForumReopened($this->tenant(), $this->forumId(), $this->exclusiveOwner())
        );
    }

    public function startDiscussion(
        ForumIdentityService $aForumIdentityService,
        Author $anAuthor,
        $aSubject
    ) {
        return $this->startDiscussionFor($aForumIdentityService, $anAuthor, $aSubject, null);
    }

    public function startDiscussionFor(
        ForumIdentityService $aForumIdentityService,
        Author $anAuthor,
        $aSubject,
        $anExclusiveOwner
    ) {
        if ($this->isClosed()) {
            throw new BadMethodCallException('Forum is closed.');
        }

        $discussion = Discussion::create(
            $this->tenant(),
            $this->forumId(),
            $aForumIdentityService->nextDiscussionId(),
            $anAuthor,
            $aSubject,
            $anExclusiveOwner
        );

        return $discussion;
    }

    public function subject()
    {
        return $this->subject;
    }

    public function tenant()
    {
        return $this->tenant;
    }

    public function equals($anObject)
    {
        $equalObjects = false;

        if (null !== $anObject && get_class($this) == get_class($anObject)) {
            $equalObjects =
                $this->tenant()->equals($anObject->tenant()) &&
                $this->forumId()->equals($anObject->forumId())
            ;
        }

        return $equalObjects;
    }

    public function __toString()
    {
        return 'Forum [closed=' . $this->closed . ', creator=' . $this->creator
        . ', description=' . $this->description . ', exclusiveOwner='. $this->exclusiveOwner
        . ', forumId=' . $this->forumId . ', moderator=' . $this->moderator
        . ', subject=' . $this->subject . ', tenantId=' . $this->tenant . ']';
    }

    protected function whenForumClosed(ForumClosed $anEvent)
    {
        $this->setClosed(true);
    }

    protected function whenForumDescriptionChanged(ForumDescriptionChanged $anEvent)
    {
        $this->setDescription($anEvent->description());
    }

    protected function whenForumModeratorChanged(ForumModeratorChanged $anEvent)
    {
        $this->setModerator($anEvent->moderator());
    }

    protected function whenForumReopened(ForumReopened $anEvent)
    {
        $this->setClosed(false);
    }

    protected function whenForumStarted(ForumStarted $anEvent)
    {
        $this->setCreator($anEvent->creator());
        $this->setDescription($anEvent->description());
        $this->setExclusiveOwner($anEvent->exclusiveOwner());
        $this->setForumId($anEvent->forumId());
        $this->setModerator($anEvent->moderator());
        $this->setSubject($anEvent->subject());
        $this->setTenant($anEvent->tenant());
        $this->setClosed(false);
    }

    protected function whenForumSubjectChanged(ForumSubjectChanged $anEvent)
    {
        $this->setSubject($anEvent->subject());
    }

    private function setClosed($isClosed)
    {
        $this->closed = $isClosed;
    }

    private function setCreator(Creator $aCreator)
    {
        $this->creator = $aCreator;
    }

    private function setDescription($aDescription)
    {
        $this->description = $aDescription;
    }

    private function setExclusiveOwner($anExclusiveOwner)
    {
        $this->exclusiveOwner = $anExclusiveOwner;
    }

    private function setForumId(ForumId $aForumId)
    {
        $this->forumId = $aForumId;
    }

    private function setModerator(Moderator $aModerator)
    {
        $this->moderator = $aModerator;
    }

    private function setSubject($aSubject)
    {
        $this->subject = $aSubject;
    }

    private function setTenant(Tenant $aTenant)
    {
        $this->tenant = $aTenant;
    }
}
