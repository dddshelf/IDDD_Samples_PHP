<?php

namespace SaasOvation\Common\Port\Adapter\Persistence\LevelDB;

use Exception;
use LevelDB;

class RepairTool
{
    private static $CONTIGUOUS_MISSING_KEY_SAFE_COUNT = 100000;

    private $lastConfirmedSequence;

    /**
     * @var LevelDB
     */
    private $database;

    public function __construct(LevelDB $database)
    {
        $this->database = $database;
    }

    public function database()
    {
        return $this->database;
    }

    public function lastConfirmedSequence()
    {
        return $this->lastConfirmedSequence;
    }

    public function repairEventStore()
    {
        if (!$this->requiresRepairProbe()) {
            return;
        }

        $cleanUpMode = false;
        $contiguousMissingKeys = 0;
        $done = false;
        $lastContiguousConfirmedKey = 0;

        for ($idSequence = 1; !$done; ++$idSequence) {
            $sequenceKey = LevelDBEventStore::$PRIMARY . $idSequence;

            $rawSequenceValue = $this->database()->get($sequenceKey);

            if (false === $rawSequenceValue) {
                $cleanUpMode = true;

                if (++$contiguousMissingKeys >= static::$CONTIGUOUS_MISSING_KEY_SAFE_COUNT) {
                    $done = true;
                }

            } else {
                if (!$cleanUpMode) {
                    $lastContiguousConfirmedKey = $idSequence;
                } else {
                    $contiguousMissingKeys = 0;

                    try {
                        $this->database()->delete($sequenceKey);
                    } catch (Exception $t) {
                        // echo 'Could not repair journal entry: ' . $idSequence;
                    }
                }
            }
        }

        $this->setLastConfirmedSequence($lastContiguousConfirmedKey);
    }

    private function setLastConfirmedSequence($aLastConfirmedSequence)
    {
        $this->lastConfirmedSequence = $aLastConfirmedSequence;
    }

    private function requiresRepairProbe()
    {
        $requiresRepairProbe = false;

        $firstSequence = $this->database()->get(LevelDBEventStore::$PRIMARY . 1);

        if (null !== $firstSequence) {
            $requiresRepairProbe = true;
        }

        return $requiresRepairProbe;
    }
}
