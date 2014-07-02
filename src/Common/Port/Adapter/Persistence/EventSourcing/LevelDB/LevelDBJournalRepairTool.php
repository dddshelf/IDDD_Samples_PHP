<?php

namespace SaasOvation\Common\Port\Adapter\Persistence\EventSourcing\LevelDB;

use Exception;
use LevelDB;
use LevelDBIterator;
use LevelDBWriteBatch;
use RuntimeException;

class LevelDBJournalRepairTool
{
    private static $CONTIGUOUS_MISSING_KEY_SAFE_COUNT = 100000;

    /**
     * @var LevelDB
     */
    private $database;

    /**
     * @var int
     */
    private $lastConfirmedSequence;

    public function __construct(LevelDB $aDatabase)
    {
        $this->setDatabase($aDatabase);
    }

    public function lastConfirmedSequence()
    {
        return $this->lastConfirmedSequence;
    }

    public function repairDatabase()
    {
        if (!$this->requiresRepair()) {
            return;
        }

        $cleanUpMode = false;
        $contiguousMissingKeys = 0;
        $done = false;
        $lastContiguousConfirmedKey = 0;

        for ($journalSequence = 1; !$done; ++$journalSequence) {
            $journalKey = (JournalKeyProvider::$ES_JOURNAL_PREFIX_KEY . $journalSequence);

            $rawJournalValue = $this->database()->get($journalKey);

            if (false === $rawJournalValue) {
                $cleanUpMode = true;

                if (++$contiguousMissingKeys >= static::$CONTIGUOUS_MISSING_KEY_SAFE_COUNT) {
                    $done = true;
                }

            } else {
                if (!$cleanUpMode) {
                    $lastContiguousConfirmedKey = $journalSequence;
                } else {
                    $contiguousMissingKeys = 0;

                    $loggedJournalEntry = new LoggedJournalEntry(
                        $journalSequence,
                        null,
                        $rawJournalValue
                    );

                    $streamKey = $loggedJournalEntry->nextMetadataValue();

                    $batch = new LevelDBWriteBatch();

                    try {
                        $batch->delete($streamKey);
                        $batch->delete($journalKey);
                        $this->database()->write($batch);

                    } catch (Exception $t) {
                        throw new RuntimeException('Could not repair journal entry: ' . $journalSequence . ' and stream entry: ' . $streamKey);
                    }
                }
            }
        }

        $this->setLastConfirmedSequence($lastContiguousConfirmedKey);
    }

    private function database()
    {
        return $this->database;
    }

    private function setDatabase(LevelDB $aDatabase)
    {
        $this->database = $aDatabase;
    }

    private function setLastConfirmedSequence($aLastConfirmedSequence)
    {
        $this->lastConfirmedSequence = $aLastConfirmedSequence;
    }

    private function requiresRepair()
    {
        $requiresRepair = false;

        $iterator = new LevelDBIterator($this->database());

        $iterator->rewind();

        if ($iterator->valid()) {
            $requiresRepair = true;
        }

        return $requiresRepair;
    }
}
