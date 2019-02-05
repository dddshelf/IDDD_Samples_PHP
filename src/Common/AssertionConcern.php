<?php

namespace SaasOvation\Common;

use InvalidArgumentException;

class AssertionConcern
{
    protected function assertArgumentEquals($anObject1, $anObject2, $aMessage)
    {
        if ($anObject1 != $anObject2) {
            throw new InvalidArgumentException($aMessage);
        }
    }

    protected function assertArgumentFalse($aBoolean, $aMessage)
    {
        if ($aBoolean) {
            throw new InvalidArgumentException($aMessage);
        }
    }

    protected function assertArgumentGreaterThan($aString, $aMaximum, $aMessage)
    {
        $length = strlen(trim($aString));

        if ($length > $aMaximum) {
            throw new InvalidArgumentException($aMessage);
        }
    }

    protected function assertArgumentBetween($aString, $aMinimum, $aMaximum, $aMessage)
    {
        $length = strlen(trim($aString));

        if ($length < $aMinimum || $length > $aMaximum) {
            throw new InvalidArgumentException($aMessage);
        }
    }

    protected function assertArgumentNotEmpty($aString, $aMessage)
    {
        if (null === $aString || empty($aString)) {
            throw new InvalidArgumentException($aMessage);
        }
    }

    protected function assertArgumentNotEquals($anObject1, $anObject2, $aMessage)
    {
        if ($anObject1 == $anObject2) {
            throw new InvalidArgumentException($aMessage);
        }
    }

    protected function assertArgumentNotNull($anObject, $aMessage)
    {
        if (null === $anObject) {
            throw new InvalidArgumentException($aMessage);
        }
    }

    protected function assertArgumentRange($aValue, $aMinimum, $aMaximum, $aMessage)
    {
        if ($aValue < $aMinimum || $aValue > $aMaximum) {
            throw new InvalidArgumentException($aMessage);
        }
    }

    protected function assertArgumentTrue($aBoolean, $aMessage)
    {
        if (!$aBoolean) {
            throw new InvalidArgumentException($aMessage);
        }
    }

    protected function assertStateFalse($aBoolean, $aMessage)
    {
        $this->assertArgumentFalse($aBoolean, $aMessage);
    }

    protected function assertStateTrue($aBoolean, $aMessage)
    {
        $this->assertArgumentTrue($aBoolean, $aMessage);
    }

    protected function assertArgumentLength($aString, $aMaximum, $aMessage)
    {
        $length = strlen(trim($aString));

        if ($length > $aMaximum) {
            throw new InvalidArgumentException($aMessage);
        }
    }

    protected function assertArgumentIsAnEmailAddress($anEmailAddress, $aMessage)
    {
        if (false === filter_var($anEmailAddress, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException($aMessage);
        }
    }
}
