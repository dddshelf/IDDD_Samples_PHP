<?php

namespace SaasOvation\Collaboration\Application\Forum\Data;

class PostData
{
    private $authorEmailAddress;
    private $authorIdentity;
    private $authorName;
    private $bodyText;
    private $changedOn;
    private $createdOn;
    private $discussionId;
    private $forumId;
    private $postId;
    private $replyToPostId;
    private $subject;
    private $tenantId;

    /**
     * @return mixed
     */
    public function getAuthorEmailAddress()
    {
        return $this->authorEmailAddress;
    }

    /**
     * @param mixed $authorEmailAddress
     */
    public function setAuthorEmailAddress($authorEmailAddress)
    {
        $this->authorEmailAddress = $authorEmailAddress;
    }

    /**
     * @return mixed
     */
    public function getAuthorIdentity()
    {
        return $this->authorIdentity;
    }

    /**
     * @param mixed $authorIdentity
     */
    public function setAuthorIdentity($authorIdentity)
    {
        $this->authorIdentity = $authorIdentity;
    }

    /**
     * @return mixed
     */
    public function getAuthorName()
    {
        return $this->authorName;
    }

    /**
     * @param mixed $authorName
     */
    public function setAuthorName($authorName)
    {
        $this->authorName = $authorName;
    }

    /**
     * @return mixed
     */
    public function getBodyText()
    {
        return $this->bodyText;
    }

    /**
     * @param mixed $bodyText
     */
    public function setBodyText($bodyText)
    {
        $this->bodyText = $bodyText;
    }

    /**
     * @return mixed
     */
    public function getChangedOn()
    {
        return $this->changedOn;
    }

    /**
     * @param mixed $changedOn
     */
    public function setChangedOn($changedOn)
    {
        $this->changedOn = $changedOn;
    }

    /**
     * @return mixed
     */
    public function getCreatedOn()
    {
        return $this->createdOn;
    }

    /**
     * @param mixed $createdOn
     */
    public function setCreatedOn($createdOn)
    {
        $this->createdOn = $createdOn;
    }

    /**
     * @return mixed
     */
    public function getDiscussionId()
    {
        return $this->discussionId;
    }

    /**
     * @param mixed $discussionId
     */
    public function setDiscussionId($discussionId)
    {
        $this->discussionId = $discussionId;
    }

    /**
     * @return mixed
     */
    public function getForumId()
    {
        return $this->forumId;
    }

    /**
     * @param mixed $forumId
     */
    public function setForumId($forumId)
    {
        $this->forumId = $forumId;
    }

    /**
     * @return mixed
     */
    public function getPostId()
    {
        return $this->postId;
    }

    /**
     * @param mixed $postId
     */
    public function setPostId($postId)
    {
        $this->postId = $postId;
    }

    /**
     * @return mixed
     */
    public function getReplyToPostId()
    {
        return $this->replyToPostId;
    }

    /**
     * @param mixed $replyToPostId
     */
    public function setReplyToPostId($replyToPostId)
    {
        $this->replyToPostId = $replyToPostId;
    }

    /**
     * @return mixed
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param mixed $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    /**
     * @return mixed
     */
    public function getTenantId()
    {
        return $this->tenantId;
    }

    /**
     * @param mixed $tenantId
     */
    public function setTenantId($tenantId)
    {
        $this->tenantId = $tenantId;
    }
}