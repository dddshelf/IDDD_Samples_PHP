<?php

namespace SaasOvation\Collaboration\Application\Forum\Data;

class ForumDiscussionsData
{
    private $closed;
    private $creatorEmailAddress;
    private $creatorIdentity;
    private $creatorName;
    private $description;
    private $exclusiveOwner;
    private $discussions = [];
    private $forumId;
    private $moderatorEmailAddress;
    private $moderatorIdentity;
    private $moderatorName;
    private $subject;
    private $tenantId;

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
    public function getCreatorEmailAddress()
    {
        return $this->creatorEmailAddress;
    }

    /**
     * @param mixed $creatorEmailAddress
     */
    public function setCreatorEmailAddress($creatorEmailAddress)
    {
        $this->creatorEmailAddress = $creatorEmailAddress;
    }

    /**
     * @return mixed
     */
    public function getCreatorIdentity()
    {
        return $this->creatorIdentity;
    }

    /**
     * @param mixed $creatorIdentity
     */
    public function setCreatorIdentity($creatorIdentity)
    {
        $this->creatorIdentity = $creatorIdentity;
    }

    /**
     * @return mixed
     */
    public function getCreatorName()
    {
        return $this->creatorName;
    }

    /**
     * @param mixed $creatorName
     */
    public function setCreatorName($creatorName)
    {
        $this->creatorName = $creatorName;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return array
     */
    public function getDiscussions()
    {
        return $this->discussions;
    }

    /**
     * @param array $discussions
     */
    public function setDiscussions($discussions)
    {
        $this->discussions = $discussions;
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
    public function getModeratorEmailAddress()
    {
        return $this->moderatorEmailAddress;
    }

    /**
     * @param mixed $moderatorEmailAddress
     */
    public function setModeratorEmailAddress($moderatorEmailAddress)
    {
        $this->moderatorEmailAddress = $moderatorEmailAddress;
    }

    /**
     * @return mixed
     */
    public function getModeratorIdentity()
    {
        return $this->moderatorIdentity;
    }

    /**
     * @param mixed $moderatorIdentity
     */
    public function setModeratorIdentity($moderatorIdentity)
    {
        $this->moderatorIdentity = $moderatorIdentity;
    }

    /**
     * @return mixed
     */
    public function getModeratorName()
    {
        return $this->moderatorName;
    }

    /**
     * @param mixed $moderatorName
     */
    public function setModeratorName($moderatorName)
    {
        $this->moderatorName = $moderatorName;
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