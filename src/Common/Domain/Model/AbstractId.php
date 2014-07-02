<?php

namespace SaasOvation\Common\Domain\Model;

use Serializable;
use SaasOvation\Common\AssertionConcern;

class AbstractId
    extends AssertionConcern
    implements Identity, Serializable
{
    /**
     * @var string
     */
    private $id;

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

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        return serialize(['id' => $this->id]);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     */
    public function unserialize($serialized)
    {
        $unserializedId = unserialize($serialized);

        $this->setId($unserializedId['id']);
    }
}
