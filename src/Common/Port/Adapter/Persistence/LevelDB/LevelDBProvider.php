<?php

namespace SaasOvation\Common\Port\Adapter\Persistence\LevelDB;

use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use PhpCollection\Map;
use RuntimeException;
use SaasOvation\Common\Event\Sourcing\EventStoreException;

use LevelDB;
use LevelDBIterator;

class LevelDBProvider
{
    /**
     * @var LevelDBProvider
     */
    private static $instance;

    /**
     * @var Map
     */
    private $databases;

    public static function instance()
    {
        if (null == self::$instance) {
            self::$instance = new LevelDBProvider();
        }

        return self::$instance;
    }

    public function close($aDirectoryPath)
    {
        $db = $this->databases->get($aDirectoryPath)->getOrElse(null);

        if (null !== $db) {
            $this->databases->remove($aDirectoryPath);

            try {
                $db->close();
            } catch (Exception $e) {
                throw new RuntimeException(
                    'Cannot completely close LevelDB database: ' . $aDirectoryPath . ' because: ' . $e->getMessage(),
                    $e->getCode(),
                    $e
                );
            }
        }
    }

    public function closeAll()
    {
        $directoryPaths = new ArrayCollection($this->databases->keys());

        foreach ($directoryPaths as $directoryPath) {
            $this->close($directoryPath);
        }
    }

    public function databaseFrom($aDirectoryPath)
    {
        $self = $this;

        $db = $this->databases->get($aDirectoryPath);

        return $db->getOrCall(function() use ($aDirectoryPath, $self) {
            $db = $this->openDatabase($aDirectoryPath);

            $self->databases->set($aDirectoryPath, $db);

            return $db;
        });
    }

    private function __construct()
    {
        $this->databases = new Map();
    }

    private function openDatabase($aDirectoryPath)
    {
        $db = null;

        try {
            $db = new LevelDB($aDirectoryPath, [
                'create_if_missing' => true
            ]);
        } catch (Exception $t) {
            throw new RuntimeException(
                'Cannot open LevelDB database: ' . $aDirectoryPath . ' because: ' . $t->getMessage(),
                $t->getCode(),
                $t
            );
        }

        return $db;
    }
}
