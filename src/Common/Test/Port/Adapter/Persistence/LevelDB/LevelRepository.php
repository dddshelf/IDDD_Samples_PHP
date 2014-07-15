<?php

namespace SaasOvation\Common\Test\Port\Adapter\Persistence\LevelDB;

use Doctrine\Common\Collections\ArrayCollection;
use SaasOvation\Common\Port\Adapter\Persistence\LevelDB\AbstractLevelDBRepository;
use SaasOvation\Common\Port\Adapter\Persistence\LevelDB\LevelDBKey;
use SaasOvation\Common\Port\Adapter\Persistence\LevelDB\LevelDBUnitOfWork;

class LevelRepository extends AbstractLevelDBRepository
{
    private static $PRIMARY         = 'LEVEL#PK';
    private static $ALL_LEVELS      = 'LEVEL#ALL';
    private static $NAME_OF_LEVEL   = 'LEVEL#NAME';

    public function __construct()
    {
        parent::__construct(LevelDBTest::$TEST_DATABASE);
    }

    public function allLevels()
    {
        $levels = new ArrayCollection();

        $allLevelsKey = LevelDBKey::createFromCategoryAndSegments(static::$ALL_LEVELS);

        $uow = LevelDBUnitOfWork::readOnly($this->database());

        $keys = $uow->readKeys($allLevelsKey);

        foreach ($keys as $levelId) {
            $level = $uow->readObjectFromString($levelId, Level::class);

            if (null !== $level) {
                $levels->add($level);
            }
        }

        return $levels;
    }

    public function levelOfName($aName)
    {
        $level = null;

        $nameKey = LevelDBKey::createFromCategoryAndSegments(self::$NAME_OF_LEVEL, $aName);

        $levelId = LevelDBUnitOfWork::readOnly($this->database())->readKey($nameKey);

        if (null !== $levelId) {
            $uow = LevelDBUnitOfWork::readOnly($this->database());

            $level = $uow->readObjectFromString($levelId, Level::class);
        }

        return $level;
    }

    public function levelOfId($anId)
    {
        $primaryKey = LevelDBKey::createFromCategoryAndSegments(self::$PRIMARY, $anId);

        $level = LevelDBUnitOfWork::readOnly($this->database())->readObjectFromString($primaryKey->key(), Level::class);

        return $level;
    }

    public function save(Level $aLevel)
    {
        $aUoW = LevelDBUnitOfWork::current();

        $primaryKey = LevelDBKey::createFromCategoryAndSegments(self::$PRIMARY, $aLevel->id());
        $aUoW->write($primaryKey, $aLevel);

        $allLevels = LevelDBKey::createFromPrimaryKey($primaryKey, self::$ALL_LEVELS);
        $aUoW->updateKeyReference($allLevels);

        $nameOfLevel = LevelDBKey::createFromPrimaryKey($primaryKey, self::$NAME_OF_LEVEL, $aLevel->name());
        $aUoW->updateKeyReference($nameOfLevel);
    }

    public function remove(Level $aLevel)
    {
        $aUoW = LevelDBUnitOfWork::current();

        $primaryKey = LevelDBKey::createFromCategoryAndSegments(self::$PRIMARY, $aLevel->id());
        $aUoW->remove($primaryKey);

        $allLevels = LevelDBKey::createFromPrimaryKey($primaryKey, self::$ALL_LEVELS);
        $aUoW->removeKeyReference($allLevels);

        $nameOfLevel = LevelDBKey::createFromPrimaryKey($primaryKey, self::$NAME_OF_LEVEL, $aLevel->name());
        $aUoW->removeKeyReference($nameOfLevel);
    }
}
