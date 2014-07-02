<?php

namespace SaasOvation\Common\Port\Adapter\Persistence\EventSourcing\LevelDB;

class LoggedJournalEntry
{
    /**
     * @var int
     */
    private $currentMetadataIndex;

    /**
     * @var int
     */
    private $journalSequence;

    /**
     * @var string
     */
    private $referenceKey;

    /**
     * @var string
     */
    private $value;

    public function __construct($aJournalSequence, $aReferenceKey, $aValue)
    {
        $this->setCurrentMetadataIndex(0);
        $this->setJournalSequence($aJournalSequence);
        $this->setReferenceKey($aReferenceKey);
        $this->setValue($aValue);
    }

    public function discardNextMetadataValue()
    {
        $this->nextMetadataValue();
    }

    public function journalSequence()
    {
        return $this->journalSequence;
    }

    public function nextMetadataValue()
    {
        $metadataValue = null;

        $value = $this->value();

        $nextIndex = strpos($value, LevelDBJournal::$ES_METADATA_DELIMITER, 0);

        if (false !== $nextIndex) {
            $metadataValue = substr($value, 0, $nextIndex);

            $this->setCurrentMetadataIndex($nextIndex + 1);
        }

        return $metadataValue;
    }

    public function referenceKey()
    {
        return $this->referenceKey;
    }

    public function value()
    {
        $index = $this->currentMetadataIndex();

        $this->setValue(substr($this->value, $index));

        $this->setCurrentMetadataIndex(0);

        return $this->value;
    }

    private function currentMetadataIndex()
    {
        return $this->currentMetadataIndex;
    }

    private function setCurrentMetadataIndex($anIndex)
    {
        $this->currentMetadataIndex = $anIndex;
    }

    private function setJournalSequence($aJournalSequence)
    {
        $this->journalSequence = $aJournalSequence;
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
