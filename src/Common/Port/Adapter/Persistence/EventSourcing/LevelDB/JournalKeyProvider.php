<?php

namespace SaasOvation\Common\Port\Adapter\Persistence\EventSourcing\LevelDB;

use BadMethodCallException;

class JournalKeyProvider
{
    public static $ES_JOURNAL_PREFIX_KEY     = 'ES_J:';
    public static $ES_KEY_DELIMITER          = ':';
    public static $ES_REFERENCE_PREFIX_KEY   = 'ES_R:';

    public function compositeReferenceKeyFrom($aKeyPart1, $aKeyPart2)
    {
        $referenceKey = $this->referenceKeyFrom($aKeyPart1 . static::$ES_KEY_DELIMITER . $aKeyPart2);
    
        return $referenceKey;
    }

    public function firstKeyPart($aCompositeKey)
    {
        return substr($aCompositeKey, 0, strpos($aCompositeKey, static::$ES_KEY_DELIMITER));
    }

    public function lastKeyPart($aCompositeKey)
    {
        return substr($aCompositeKey, strrpos($aCompositeKey, static::$ES_KEY_DELIMITER) + 1);
    }

    public function nextReferenceKey()
    {
        throw new BadMethodCallException('Must be implemented by subclass.');
    }

    public function primaryResourceName()
    {
        throw new BadMethodCallException('Must be implemented by subclass.');
    }

    public function referenceKeyFrom($aKey)
    {
        $referenceKey = static::$ES_REFERENCE_PREFIX_KEY . $aKey;

        return $referenceKey;
    }
}
