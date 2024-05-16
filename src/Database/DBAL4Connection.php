<?php
/**
 * PHP version 7.1
 *
 * This source file is subject to the license that is bundled with this package in the file LICENSE.
 */
namespace ComPHPPuebla\Fixtures\Database;

use Doctrine\DBAL\Connection as DbConnection;
use Doctrine\DBAL\Exception;

class DBAL4Connection implements Connection
{
    /**
     * @var DbConnection $connection
     */
    var $connection;
    public function __construct(DbConnection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param string $table
     * @param Row $row
     * @throws \Doctrine\DBAL\DBALException
     */
    public function insert(string $table, Row $row): void
    {
        $insert = Insert::into($table, $row);

        $this->connection->executeStatement($insert->toSQL($this->connection), $insert->parameters());

        try {
            $id = $this->connection->lastInsertId();
            $row->assignId($id);
        } catch (\Exception $e) {};
    }

    /**
     * This method is needed in order to keep track of references to other rows in the fixtures
     *
     * @see \ComPHPPuebla\Fixtures\Fixture::processTableRows
     */
    public function primaryKeyOf(string $table): string
    {
        $schema = $this->connection->createSchemaManager();
        $tbl = $schema->introspectTable($table);
        $primaryKeyIndex = $tbl->getPrimaryKey();
        if (empty($primaryKeyIndex)) {
            throw new \Exception("No Primary key for $table");
        }
        $primaryKeyCols = $primaryKeyIndex->getColumns();
        return $primaryKeyCols[0];
    }

    /**
     * Use this method for types not supported by default by DBAL, like MySQL enums. For instance:
     *
     * `$connection->registerPlatformType('enum', 'string');`
     *
     * @throws Exception
     */
    public function registerPlatformType(string $platformType, string $dbalType): void
    {
        $this->connection->getDatabasePlatform()->registerDoctrineTypeMapping($platformType, $dbalType);
    }
}
