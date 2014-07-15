<?php

namespace SaasOvation\Common\Port\Adapter\Persistence\LevelDB;

use Doctrine\Common\Collections\ArrayCollection;
use LevelDBWriteBatch;
use LevelDB;

use PhpCollection\Map;
use SaasOvation\Common\Serializer\ObjectSerializer;
use UnexpectedValueException;

class LevelDBUnitOfWork
{
    /**
     * @var LevelDBUnitOfWork
     */
    private static $unitOfWork;

    /**
     * @var LevelDBWriteBatch
     */
    private $batch;

    /**
     * @var LevelDB
     */
    private $database;

    /**
     * @var Map
     */
    private $referenceKeys;

    /**
     * @var ObjectSerializer
     */
    private $serializer;

    public static function current()
    {
        if (null === self::$unitOfWork) {
            throw new UnexpectedValueException('No unit of work has been started.');
        }

        return self::$unitOfWork;
    }

    public static function readOnly(LevelDB $aDatabase)
    {
        if (null === self::$unitOfWork) {
            self::$unitOfWork = new LevelDBUnitOfWork($aDatabase, false);
        }

        return self::$unitOfWork;
    }

    public static function start(LevelDB $aDatabase)
    {
        if (null === self::$unitOfWork) {
            self::$unitOfWork = new LevelDBUnitOfWork($aDatabase);
        } else {
            self::$unitOfWork->createWriteBatch();
        }

        return self::$unitOfWork;
    }

    public function commit()
    {
        $this->database->write($this->batch);

        $this->close();
    }

    public function readObjectAsBytes(LevelDBKey $aKey)
    {
        return $this->database->get($aKey->key());
    }

    public function readObject(LevelDBKey $aKey, $aType)
    {
        return $this->readObjectFromString($aKey->key(), $aType);
    }

    public function readObjectFromString($aKey, $aType)
    {
        $objectBytes = $this->database->get($aKey);

        if (false !== $objectBytes) {
            return $this->serializer->deserialize($objectBytes, $aType);
        }
    }

    public function readKey(LevelDBKey $aKey)
    {
        $singleKey = null;

        $keys = $this->loadReferenceKeyValues($aKey);

        if (!$keys->isEmpty()) {
            $singleKey = $keys->current();
        }

        return $singleKey;
    }

    public function readKeys(LevelDBKey $aKey)
    {
        return $this->loadReferenceKeyValues($aKey);
    }

    public function remove(LevelDBKey $aPrimaryKey)
    {
        $this->batch->delete($aPrimaryKey->key());
    }

    public function removeKeyReference(LevelDBKey $aKey)
    {
        $allValues = $this->loadReferenceKeyValues($aKey);

        if ($allValues->removeElement($aKey->primaryKeyValue())) {
            if ($allValues->isEmpty()) {
                $this->batch->delete($aKey->key());
            } else {
                $serializedValue = $this->serializer->serialize($allValues);

                $this->batch->put($aKey->key(), $serializedValue);
            }
        }
    }

    public function rollback()
    {
        $this->close();
    }

    public function updateKeyReference(LevelDBKey $aKey)
    {
        $allValues = $this->loadReferenceKeyValues($aKey);

        $allValues->add($aKey->primaryKeyValue());

        $serializedValue = $this->serializer->serialize($allValues);

        $this->batch->put($aKey->keyAsBytes(), $serializedValue);
    }

    public function write($aKey, $aValue)
    {
        $serializedValue = $this->serializer->serialize($aValue);

        if ($aKey instanceof LevelDBKey) {
            $aKey = $aKey->key();
        }

        $this->batch->put($aKey, $serializedValue);
    }

    private function __construct(LevelDB $aDatabase, $isWritable = true)
    {
        if ($isWritable) {
            $this->createWriteBatch();
        }

        $this->database = $aDatabase;
        $this->referenceKeys = new Map();
        $this->serializer = ObjectSerializer::instance();
    }

    private function createWriteBatch()
    {
        if (null === $this->batch) {
            $this->batch = new LevelDBWriteBatch();
        }
    }

    private function close()
    {
        self::$unitOfWork = null;

        if (null !== $this->batch) {
            $this->batch = null;
        }
    }

    private function loadReferenceKeyValues(LevelDBKey $aKey)
    {
        return $this->referenceKeys->get($aKey->key())->getOrCall(function() use ($aKey) {
            $currentValues = $this->database->get($aKey->key());

            $values = [];

            if (false !== $currentValues) {
                $values = $this->serializer->deserialize($currentValues, 'array');
            }

            $allValues = new ArrayCollection($values);

            $this->referenceKeys->set($aKey->key(), $allValues);

            return $allValues;
        });
    }
}
