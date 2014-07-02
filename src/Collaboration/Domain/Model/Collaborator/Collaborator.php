<?php

namespace SaasOvation\Collaboration\Domain\Model\Collaborator;

use Serializable;

abstract class Collaborator
    implements Serializable
{
    private $emailAddress;
    private $identity;
    private $name;

    public function __construct($anIdentity, $aName, $anEmailAddress)
    {
        $this->setEmailAddress($anEmailAddress);
        $this->setIdentity($anIdentity);
        $this->setName($aName);
    }

    public function emailAddress()
    {
        return $this->emailAddress;
    }

    public function identity()
    {
        return $this->identity;
    }

    public function name()
    {
        return $this->name;
    }

    public function compareTo(Collaborator $aCollaborator)
    {
        $diff = $this->identity()->compareTo($aCollaborator->identity());

        if ($diff == 0) {
            $diff = $this->emailAddress()->compareTo($aCollaborator->emailAddress());

            if ($diff == 0) {
                $diff = $this->name()->compareTo($aCollaborator->name());
            }
        }

        return $diff;
    }

    public function equals($anObject)
    {
        $equalObjects = false;

        if (null !== $anObject && get_class($this) == get_class($anObject)) {
            $equalObjects =
                $this->emailAddress() === $anObject->emailAddress() &&
                $this->identity() === $anObject->identity() &&
                $this->name() === $anObject->name()
            ;
        }

        return $equalObjects;
    }

    public function __toString()
    {
        return get_class($this) .
        ' [emailAddress=' . $this->emailAddress . ', identity=' . $this->identity . ', name=' . $this->name . ']';
    }

    private function setEmailAddress($anEmailAddress)
    {
        $this->emailAddress = $anEmailAddress;
    }

    private function setIdentity($anIdentity)
    {
        $this->identity = $anIdentity;
    }

    private function setName($aName)
    {
        $this->name = $aName;
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        return serialize([
            'identity'      => $this->identity,
            'name'          => $this->name,
            'emailAddress'  => $this->emailAddress
        ]);
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
        $raw = unserialize($serialized);

        $this->setIdentity($raw['identity']);
        $this->setName($raw['name']);
        $this->setEmailAddress($raw['emailAddress']);
    }
}
