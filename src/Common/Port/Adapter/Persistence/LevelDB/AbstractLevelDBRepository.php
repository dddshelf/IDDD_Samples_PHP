<?php

namespace SaasOvation\Common\Port\Adapter\Persistence\LevelDB;

use LevelDB;

abstract class AbstractLevelDBRepository
{
    /**
     * @var LevelDB
     */
    private $database;

    /**
     * @var string
     */
    private $databasePath;

    public function __construct($aDirectoryPath)
    {
        $this->openDatabase($aDirectoryPath);
    }

    protected function database()
    {
        return $this->database;
    }

    protected function databasePath()
    {
        return $this->databasePath;
    }

    private function setDatabase(LevelDB $aDatabase)
    {
        $this->database = $aDatabase;
    }

    private function setDatabasePath($aDatabasePath)
    {
        $this->databasePath = $aDatabasePath;
    }

    private function openDatabase($aDirectoryPath)
    {
        $levelDBProvider = LevelDBProvider::instance();

        $db = $levelDBProvider->databaseFrom($aDirectoryPath);

        $this->setDatabase($db);
        $this->setDatabasePath($aDirectoryPath);
    }
}
