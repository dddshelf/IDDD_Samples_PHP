<?php

namespace SaasOvation\Collaboration\Domain\Model\Forum;

use SaasOvation\Collaboration\Domain\Model\Collaborator\Author;
use SaasOvation\Collaboration\Domain\Model\Tenant\Tenant;
use SaasOvation\Common\Domain\Model\EventSourcedRootEntity;
use BadMethodCallException;

class Discussion extends EventSourcedRootEntity
{
    /**
     * @var Author
     */
    private $author;

    /**
     * @var bool
     */
    private $closed;

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

    public function author()
    {
        return $this->author;
    }

    public function close()
    {
        if ($this->isClosed()) {
            throw new BadMethodCallException('This discussion is already closed.');
        }

        $this->apply(
            new DiscussionClosed($this->tenant(), $this->forumId(), $this->discussionId(), $this->exclusiveOwner())
        );
    }

    public function isClosed()
    {
        return $this->closed;
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

    public function postWithoutReply(
        ForumIdentityService $aForumIdentityService,
        Author $anAuthor,
        $aSubject,
        $aBodyText
    ) {
        return $this->postWithReply($aForumIdentityService, null, $anAuthor, $aSubject, $aBodyText);
    }

    public function postWithReply(
        ForumIdentityService $aForumIdentityService,
        $aReplyToPost,
        Author $anAuthor,
        $aSubject,
        $aBodyText
    ) {
        $post = Post::create(
            $this->tenant(),
            $this->forumId(),
            $this->discussionId(),
            $aReplyToPost,
            $aForumIdentityService->nextPostId(),
            $anAuthor,
            $aSubject,
            $aBodyText
        );

        return $post;
    }

    public function reopen()
    {
        if (!$this->isClosed()) {
            throw new BadMethodCallException('The discussion is not closed.');
        }

        $this->apply(
            new DiscussionReopened($this->tenant(), $this->forumId(), $this->discussionId(), $this->exclusiveOwner())
        );
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
                $this->forumId()->equals($anObject->forumId()) &&
                $this->discussionId()->equals($anObject->discussionId());
        }

        return $equalObjects;
    }

    public function __toString()
    {
        return 'Discussion [author=' . $this->author . ', closed=' . $this->closed . ', discussionId=' . $this->discussionId . ', exclusiveOwner='
        . $this->exclusiveOwner . ', forumId=' . $this->forumId . ', subject=' . $this->subject . ', tenantId=' . $this->tenant . ']';
    }

    private function createFrom(
        Tenant $aTenantId,
        ForumId $aForumId,
        DiscussionId $aDiscussionId,
        Author $anAuthor,
        $aSubject,
        $anExclusiveOwner
    ) {
        $this->assertArgumentNotNull($anAuthor, 'The author must be provided.');
        $this->assertArgumentNotNull($aDiscussionId, 'The discussion id must be provided.');
        $this->assertArgumentNotNull($aForumId, 'The forum id must be provided.');
        $this->assertArgumentNotEmpty($aSubject, 'The subject must be provided.');
        $this->assertArgumentNotNull($aTenantId, 'The tenant must be provided.');

        $this->apply(
            new DiscussionStarted($aTenantId, $aForumId, $aDiscussionId, $anAuthor, $aSubject, $anExclusiveOwner)
        );
    }

    public static function create(
        Tenant $aTenantId,
        ForumId $aForumId,
        DiscussionId $aDiscussionId,
        Author $anAuthor,
        $aSubject,
        $anExclusiveOwner
    ) {
        $aDiscussion = new Discussion();

        $aDiscussion->createFrom(
            $aTenantId,
            $aForumId,
            $aDiscussionId,
            $anAuthor,
            $aSubject,
            $anExclusiveOwner
        );

        return $aDiscussion;
    }

    protected function whenDiscussionClosed(DiscussionClosed $anEvent)
    {
        $this->setClosed(true);
    }

    protected function whenDiscussionReopened(DiscussionReopened $anEvent)
    {
        $this->setClosed(false);
    }

    protected function whenDiscussionStarted(DiscussionStarted $anEvent)
    {
        $this->setAuthor($anEvent->author());
        $this->setDiscussionId($anEvent->discussionId());
        $this->setExclusiveOwner($anEvent->exclusiveOwner());
        $this->setForumId($anEvent->forumId());
        $this->setSubject($anEvent->subject());
        $this->setTenant($anEvent->tenant());
        $this->setClosed(false);
    }

    private function setAuthor(Author $author)
    {
        $this->author = $author;
    }

    private function setClosed($isClosed)
    {
        $this->closed = $isClosed;
    }

    private function setDiscussionId(DiscussionId $aDiscussionId)
    {
        $this->discussionId = $aDiscussionId;
    }

    private function setExclusiveOwner($anExclusiveOwner)
    {
        $this->exclusiveOwner = $anExclusiveOwner;
    }

    private function setForumId(ForumId $aForumId)
    {
        $this->forumId = $aForumId;
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
