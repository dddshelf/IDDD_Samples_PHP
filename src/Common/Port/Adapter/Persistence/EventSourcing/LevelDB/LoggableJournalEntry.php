<?php

namespace SaasOvation\Common\Port\Adapter\Persistence\EventSourcing\LevelDB;

class LoggableJournalEntry
{
    /**
     * @var string
     */
    private $primaryResourceName;

    /**
     * @var string
     */
    private $referenceKey;

    /**
     * @var string
     */
    private $value;

    public function __construct($aValue, $aReferenceKey, $aPrimaryResourceName)
    {
        $this->setPrimaryResourceName($aPrimaryResourceName);
        $this->setReferenceKey($aReferenceKey);
        $this->setValue($aValue);
    }

    public function primaryResourceName()
    {
        return $this->primaryResourceName;
    }

    public function referenceKey()
    {
        return $this->referenceKey;
    }

    public function value()
    {
        return $this->value;
    }

    private function setPrimaryResourceName($aPrimaryResourceName)
    {
        $this->primaryResourceName = $aPrimaryResourceName;
    }

    private function setReferenceKey($aReferenceKey)
    {
        $this->referenceKey = $aReferenceKey;
    }

    private function setValue($aValue)
    {
        $this->value = $aValue;
    }
}
