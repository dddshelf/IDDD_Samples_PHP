<?php

namespace SaasOvation\Collaboration\Test;

use RuntimeException;
use Exception;
use PDO;

use SaasOvation\Collaboration\Port\Adapter\Persistence\EventStoreProvider;

class StorageCleaner extends EventStoreProvider
{
    private static $tablesToClean = [
        'tbl_dispatcher_last_event',
        'tbl_es_event_store',
        'tbl_vw_calendar',
        'tbl_vw_calendar_sharer',
        'tbl_vw_calendar_entry',
        'tbl_vw_calendar_entry_invitee',
        'tbl_vw_forum',
        'tbl_vw_discussion',
        'tbl_vw_post'
    ];

    /**
     * @var PDO
     */
    private $dataSource;

    public function __construct(PDO $aDataSource)
    {
        $this->dataSource = $aDataSource;

        parent::__construct();
    }

    public function clean()
    {
        $this->eventStore()->purge();

        try {

            foreach (static::$tablesToClean as $tableName) {
                $statement = $this->dataSource->prepare('delete from ' . $tableName);
                $statement->execute();
                $statement->closeCursor();
            }

        } catch (Exception $e) {
            throw new RuntimeException('Cannot delete tbl_dispatcher_last_event because: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }
}
