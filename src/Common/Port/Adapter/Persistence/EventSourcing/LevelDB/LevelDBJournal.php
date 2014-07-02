<?php

namespace SaasOvation\Common\Port\Adapter\Persistence\EventSourcing\LevelDB;

use BadMethodCallException;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use SaasOvation\Common\Event\Sourcing\EventStoreAppendException;
use SaasOvation\Common\Event\Sourcing\EventStoreException;
use LevelDb;
use LevelDBWriteBatch;
use LevelDBIterator;

class LevelDBJournal
{
    public static $ES_METADATA_DELIMITER = '#';

    /**
     * @var LevelDBJournal
     */
    private static $instance;

    /**
     * @var LevelDB
     */
    private $database;

    /**
     * @var string
     */
    private $databasePath;

    /**
     * @var int
     */
    private $journalSequence;

    public static function initializeInstance($aDirectoryPath)
    {
        if (null === static::$instance) {
            static::$instance = new static($aDirectoryPath);
        } else {

            // for test
            if (null === static::$instance->database()) {
                static::$instance->openDatabase($aDirectoryPath);
            }
        }

        return static::$instance;
    }

    public static function instance()
    {
        if (null === static::$instance) {
            throw new BadMethodCallException('There is no LevelDBJournalProvider instance.');
        }

        return static::initializeInstance(static::$instance->databasePath());
    }

    private static function ES_JOURNAL_SEQUENCE_KEY()
    {
        return JournalKeyProvider::$ES_JOURNAL_PREFIX_KEY . '0';
    }

    public function close()
    {
        if (null !== static::$instance) {
            try {
                $this->saveJournalSequence();

                $this->database()->close();

            } catch (Exception $t) {
                throw new EventStoreException(
                    'Cannot close LevelDB database: '
                    . $this->databasePath()
                    . ' because: '
                    . $t->getMessage(),
                    $t
                );
            } finally {
                static::$instance = null;
            }
        }
    }

    public function databasePath()
    {
        return $this->databasePath;
    }

    public function logEntries(array $aJournalEntries)
    {
        $batch = new LevelDBWriteBatch();

        try {
            foreach ($aJournalEntries as $journalEntry) {

                $journalSequence = $this->nextJournalSequence();

                $this->confirmNonExistingReference($journalEntry->referenceKey());

                $jounralKey = JournalKeyProvider::$ES_JOURNAL_PREFIX_KEY . $journalSequence;

                $referenceKey = $journalEntry->referenceKey();

                $journalSequenceBytes = $journalSequence;

                $journalValue = $this->valueWithMetadata($journalEntry->value(), $referenceKey);

                // journal entry points to reference

                $batch->put($jounralKey, $journalValue);

                // reference points to journal entry

                $batch->put($referenceKey, $journalSequenceBytes);
            }

            $this->database()->write($batch);

        } catch (Exception $t) {
            throw new EventStoreAppendException(
                'Could not append to journal because: ' . $t->getMessage(),
                $t->getCode(),
                $t
            );
        }
    }

    public function loggedJournalEntriesSince($aJournalSequence)
    {
        $entries = new ArrayCollection();

        $done = false;

        for ($journalSequence = $aJournalSequence + 1; !$done; ++$journalSequence) {
            $journalKey = JournalKeyProvider::$ES_JOURNAL_PREFIX_KEY . $journalSequence;

            $rawJournalValue = $this->database()->get($journalKey);

            if (null !== $rawJournalValue) {

                $loggedJournalEntry = new LoggedJournalEntry(
                    $journalSequence,
                    null,
                    $rawJournalValue
                );

                // discard the reference key
                $loggedJournalEntry->discardNextMetadataValue();

                $entries->add($loggedJournalEntry);
            } else {
                $done = true;
            }
        }

        return $entries;
    }

    public function purge()
    {
        $iterator = new LevelDBIterator($this->database());

        try {
            $iterator->rewind();

            foreach ($iterator as $key => $value) {
                $this->database()->delete($key);
            }

        } catch (Exception $t) {
            throw new EventStoreException(
                'Cannot purge journal LevelDB database: '
                . $this->databasePath()
                . ' because: '
                . $t->getMessage(),
                $t
            );
        } finally {
            $this->setJournalSequence(0);
        }
    }

    public function referencedLoggedJournalEntries(JournalKeyProvider $aReferenceKeyProvider)
    {
        $entries = new ArrayCollection();

        $done = false;

        while (!$done) {
            $referenceKey = $aReferenceKeyProvider->nextReferenceKey();

            $rawJournalSequenceValue = $this->database()->get($referenceKey);

            if (false !== $rawJournalSequenceValue) {
                $journalSequence = $rawJournalSequenceValue;

                $journalKey = JournalKeyProvider::$ES_JOURNAL_PREFIX_KEY . $journalSequence;

                $rawJournalValue = $this->database()->get($journalKey);

                $loggedJournalEntry = new LoggedJournalEntry(
                    $journalSequence,
                    $referenceKey,
                    $rawJournalValue
                );

                // discard the stream key
                $loggedJournalEntry->discardNextMetadataValue();

                $entries->add($loggedJournalEntry);

            } else {
                $done = true;
            }
        }

        return $entries;
    }

    public function valueWithMetadata($aValue, $aMetadata)
    {
        $valueWithMetadata = $aMetadata . static::$ES_METADATA_DELIMITER . $aValue;

        return $valueWithMetadata;
    }

    private function __construct($aDirectoryPath)
    {
        $this->openDatabase($aDirectoryPath);
    }

    private function cacheJournalSequence()
    {
        $cached = false;

        $journalSequenceValue = $this->database()->get(static::ES_JOURNAL_SEQUENCE_KEY());

        if (false !== $journalSequenceValue) {
            $this->setJournalSequence($journalSequenceValue);

            // only a successful close() will save the journal sequence.
            // a missing journal sequence on open indicates the need for
            // a repair (unless the database is empty).

            $this->database()->delete(static::ES_JOURNAL_SEQUENCE_KEY());

            $cached = true;

        } else {
            $this->setJournalSequence(0);
        }

        return $cached;
    }

    private function confirmNonExistingReference($aReferenceKey)
    {
        // $this implementation will not stand up to race conditions

        if (false !== $this->database()->get($aReferenceKey)) {
            throw new EventStoreAppendException('Journal concurrency violation.');
        }
    }

    private function database()
    {
        return $this->database;
    }

    private function setDatabase(LevelDB $aDatabase)
    {
        $this->database = $aDatabase;
    }

    private function setDatabasePath($aDatabasePath)
    {
        $this->databasePath = $aDatabasePath;
    }

    private function nextJournalSequence()
    {
        return ++$this->journalSequence;
    }

    private function setJournalSequence($aJournalSequence)
    {
        $this->journalSequence = $aJournalSequence;
    }

    private function openDatabase($aDirectoryPath)
    {
        try {
            $this->setDatabasePath($aDirectoryPath);

            $options = [
                'create_if_missing' => true
            ];

            $this->setDatabase(
                new LevelDB($this->databasePath(), $options)
            );

            if (!$this->cacheJournalSequence()) {
                $this->repair();
            }

        } catch (Exception $t) {
            throw new EventStoreException(
                'Cannot open LevelDB database: ' . $aDirectoryPath . ' because: ' . $t->getMessage(),
                $t->getCode(),
                $t
            );
        }
    }

    private function repair()
    {
        $repairTool = new LevelDBJournalRepairTool($this->database());

        $repairTool->repairDatabase();

        $lastConfirmedKey = $repairTool->lastConfirmedSequence();

        if ($lastConfirmedKey > 0) {
            $this->setJournalSequence($lastConfirmedKey);
        }
    }

    private function saveJournalSequence()
    {
        $journalSequenceBytes = $this->journalSequence->get();

        $this->database()->put(static::ES_JOURNAL_SEQUENCE_KEY(), $journalSequenceBytes);
    }
}
