<?php

namespace SaasOvation\Common\Port\Adapter\Persistence\NotORM;

use InvalidArgumentException;
use NotORM_Structure_Convention;

class TableViewsStructure extends NotORM_Structure_Convention
{
    private static $PRIMARY_KEYS_PER_TABLE = [
        'tbl_vw_calendar'                   => 'calendar_id',
        'tbl_vw_calendar_entry'             => 'calendar_entry_id',
        'tbl_vw_calendar_entry_invitee'     => 'id',
        'tbl_vw_calendar_sharer'            => 'id',
        'tbl_vw_discussion'                 => 'discussion_id',
        'tbl_vw_forum'                      => 'forum_id',
        'tbl_vw_post'                       => 'post_id',
    ];

    public function __construct()
    {
        parent::__construct(
            '%s_id',    // primary keys structure
            '%s_id',    // foreign keys structure
            '%s',       // Table name structure
            'tbl_vw_'   // Tables prefix
        );
    }

    public function getReferencedColumn($name, $table)
    {
        return sprintf(
            $this->foreign,
            $name
        );
    }

    public function getPrimary($table)
    {
        if (!isset(self::$PRIMARY_KEYS_PER_TABLE[$table])) {
            throw new InvalidArgumentException(sprintf('Unknown table "%s"', $table));
        }

        return static::$PRIMARY_KEYS_PER_TABLE[$table];
    }
}