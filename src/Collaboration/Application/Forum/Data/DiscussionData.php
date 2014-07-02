<?php

namespace SaasOvation\Collaboration\Application\Forum\Data;

class DiscussionData
{
    private $authorEmailAddress;
    private $authorIdentity;
    private $authorName;
    private $closed;
    private $discussionId;
    private $exclusiveOwner;
    private $forumId;
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
    public function isClosed()
    {
        return $this->closed;
    }

    /**
     * @param mixed $closed
     */
    public function setClosed($closed)
    {
        $this->closed = $closed;
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
    public function getExclusiveOwner()
    {
        return $this->exclusiveOwner;
    }

    /**
     * @param mixed $exclusiveOwner
     */
    public function setExclusiveOwner($exclusiveOwner)
    {
        $this->exclusiveOwner = $exclusiveOwner;
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
