<?php

namespace SaasOvation\Common\Port\Adapter\Persistence;

use PDO;
use NotORM;
use Exception;
use PDOStatement;
use BadMethodCallException;
use SaasOvation\Common\Port\Adapter\Persistence\NotORM\TableViewsStructure;

abstract class AbstractQueryService
{
    /**
     * @var NotORM
     */
    private $database;

    /**
     * @var PDO
     */
    private $connection;

    public function __construct(PDO $aConnection)
    {
        $this->connection = $aConnection;
        $this->database   = new NotORM($aConnection, new TableViewsStructure());
    }

    protected function database()
    {
        return $this->database;
    }

    protected function close()
    {
        $this->connection = null;
    }

    protected function queryString($aQuery)
    {
        $anArguments = array_slice(func_get_args(), 1);

        $value = null;

        try {
            $selectStatement = $this->connection->prepare($aQuery);

            $this->setStatementArguments($selectStatement, $anArguments);

            $selectStatement->execute();
            $value = $selectStatement->fetchColumn();

            $selectStatement->closeCursor();

        } catch (Exception $e) {
            throw new BadMethodCallException('Cannot query: ' . $aQuery, $e);
        }

        return $value;
    }

    private function setStatementArguments(PDOStatement $aPreparedStatement, array $anArguments)
    {
        for ($idx = 0; $idx < count($anArguments); ++$idx) {
            $aPreparedStatement->bindValue($idx + 1, $anArguments[$idx]);
        }
    }
}
