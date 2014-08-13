<?php

namespace SaasOvation\Common\Domain\Model;

use Serializable;
use SaasOvation\Common\AssertionConcern;

class AbstractId
    extends AssertionConcern
    implements Identity
{
    /**
     * @var string
     */
    protected $id;

    public function id()
    {
        return $this->id;
    }

    public function equals($anObject)
    {
        $equalObjects = false;

        if (null !== $anObject && get_class($this) === get_class($anObject)) {
            $equalObjects = $this->id() === $anObject->id();
        }

        return $equalObjects;
    }

    public function __toString()
    {
        return get_class($this) . " [id=" . $this->id . "]";
    }

    public function __construct($anId)
    {
        $this->setId($anId);
    }

    protected function validateId($anId)
    {
        // implemented by subclasses for validation.
        // throws a runtime exception if invalid.
    }

    private function setId($anId)
    {
        $this->assertArgumentNotEmpty($anId, 'The basic identity is required.');
        $this->assertArgumentLength($anId, 36, 'The basic identity must be 36 characters.');

        $this->validateId($anId);

        $this->id = $anId;
    }
}
