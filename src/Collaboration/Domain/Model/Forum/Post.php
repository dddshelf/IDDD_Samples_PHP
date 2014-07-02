<?php

namespace SaasOvation\Collaboration\Domain\Model\Forum;

use DateTimeInterface;
use SaasOvation\Collaboration\Domain\Model\Collaborator\Author;
use SaasOvation\Collaboration\Domain\Model\Tenant\Tenant;
use SaasOvation\Common\Domain\Model\EventSourcedRootEntity;

class Post extends EventSourcedRootEntity
{
    /**
     * @var Author
     */
    private $author;

    /**
     * @var string
     */
    private $bodyText;

    /**
     * @var DateTimeInterface
     */
    private $changedOn;

    /**
     * @var DateTimeInterface
     */
    private $createdOn;

    /**
     * @var DiscussionId
     */
    private $discussionId;

    /**
     * @var ForumId
     */
    private $forumId;

    /**
     * @var PostId
     */
    private $postId;

    /**
     * @var PostId
     */
    private $replyToPostId;

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

    public function bodyText()
    {
        return $this->bodyText;
    }

    public function changedOn()
    {
        return $this->changedOn;
    }

    public function createdOn()
    {
        return $this->createdOn;
    }

    public function discussionId()
    {
        return $this->discussionId;
    }

    public function forumId()
    {
        return $this->forumId;
    }

    public function postId()
    {
        return $this->postId;
    }

    public function replyToPostId()
    {
        return $this->replyToPostId;
    }

    public function subject()
    {
        return $this->subject;
    }

    public function tenant()
    {
        return $this->tenant;
    }

    private function createFrom(
        Tenant $aTenant,
        ForumId $aForumId,
        DiscussionId $aDiscussionId,
        $aReplyToPost,
        PostId $aPostId,
        Author $anAuthor,
        $aSubject,
        $aBodyText
    ) {

        $this->assertArgumentNotNull($anAuthor, 'The author must be provided.');
        $this->assertArgumentNotEmpty($aBodyText, 'The body text must be provided.');
        $this->assertArgumentNotNull($aDiscussionId, 'The discussion id must be provided.');
        $this->assertArgumentNotNull($aForumId, 'The forum id must be provided.');
        $this->assertArgumentNotNull($aPostId, 'The post id must be provided.');
        $this->assertArgumentNotEmpty($aSubject, 'The subject must be provided.');
        $this->assertArgumentNotNull($aTenant, 'The tenant must be provided.');

        $this->apply(
            new PostedToDiscussion($aTenant, $aForumId, $aDiscussionId, $aReplyToPost, $aPostId, $anAuthor, $aSubject, $aBodyText)
        );
    }

    public static function create(
        Tenant $aTenant,
        ForumId $aForumId,
        DiscussionId $aDiscussionId,
        $aReplyToPost,
        PostId $aPostId,
        Author $anAuthor,
        $aSubject,
        $aBodyText
    ) {
        $post = new Post();

        $post->createFrom(
            $aTenant,
            $aForumId,
            $aDiscussionId,
            $aReplyToPost,
            $aPostId,
            $anAuthor,
            $aSubject,
            $aBodyText
        );

        return $post;
    }

    public function alterPostContent($aSubject, $aBodyText)
    {
        $this->assertArgumentNotEmpty($aSubject, 'The subject must be provided.');
        $this->assertArgumentNotEmpty($aBodyText, 'The body text must be provided.');

        $this->apply(
            new PostContentAltered($this->tenant(), $this->forumId(), $this->discussionId(), $this->postId(), $aSubject, $aBodyText)
        );
    }

    protected function whenPostContentAltered(PostContentAltered $anEvent)
    {
        $this->setBodyText($anEvent->bodyText());
        $this->setChangedOn($anEvent->occurredOn());
        $this->setSubject($anEvent->subject());
    }

    protected function whenPostedToDiscussion(PostedToDiscussion $anEvent)
    {
        $this->setAuthor($anEvent->author());
        $this->setBodyText($anEvent->bodyText());
        $this->setChangedOn($anEvent->occurredOn());
        $this->setCreatedOn($anEvent->occurredOn());
        $this->setDiscussionId($anEvent->discussionId());
        $this->setForumId($anEvent->forumId());
        $this->setPostId($anEvent->postId());

        if (null !== $anEvent->replyToPost()) {
            $this->setReplyToPostId($anEvent->replyToPost());
        }

        $this->setSubject($anEvent->subject());
        $this->setTenant($anEvent->tenant());
    }

    private function setAuthor(Author $anAuthor)
    {
        $this->author = $anAuthor;
    }

    private function setBodyText($aBodyText)
    {
        $this->bodyText = $aBodyText;
    }

    private function setChangedOn(DateTimeInterface $aChangedOnDate)
    {
        $this->changedOn = $aChangedOnDate;
    }

    private function setCreatedOn(DateTimeInterface $aCreatedOnDate)
    {
        $this->createdOn = $aCreatedOnDate;
    }

    private function setDiscussionId(DiscussionId $aDiscussionId)
    {
        $this->discussionId = $aDiscussionId;
    }

    private function setForumId(ForumId $aForumId)
    {
        $this->forumId = $aForumId;
    }

    private function setPostId(PostId $aPostId)
    {
        $this->postId = $aPostId;
    }

    private function setReplyToPostId(PostId $aReplyToPostId)
    {
        $this->replyToPostId = $aReplyToPostId;
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
