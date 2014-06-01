<?php

namespace SaasOvation\AgilePm\Domain\Model\Discussion;

use SaasOvation\Common\AssertionConcern;

class DiscussionDescriptor extends AssertionConcern
{
    const UNDEFINED_ID = 'UNDEFINED';

    /**
     * @var string
     */
    private $id;

    /**
     * @param string $id
     */
    public function __construct($id)
    {
        parent::__construct();

        $this->setId($id);
    }

    public static function makeFromDiscussionDescriptor(DiscussionDescriptor $discussionDescriptor)
    {
        return new DiscussionDescriptor($discussionDescriptor->id());
    }

    public function id()
    {
        return $this->id;
    }

    public function isUndefined()
    {
        return $this->id() === self::UNDEFINED_ID;
    }

    public function equals($anObject)
    {
        $equalObjects = false;
        if ($anObject !== null && get_class($this) === get_class($anObject)) {
            $equalObjects = $this->id() === $equalObjects->id();
        }

        return $equalObjects;
    }

    public function __toString()
    {
        return "DiscussionDescriptor [id=".$this->id."]";
    }

    private function setId($id)
    {
        $this->assertArgumentNotEmpty($id, "The discussion identity must be provided.");
        $this->assertArgumentLength($id, 36, "The discussion identity must be 36 characters or less.");

        $this->id = $id;
    }
}
