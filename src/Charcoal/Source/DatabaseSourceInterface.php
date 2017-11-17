<?php

namespace Charcoal\Source;

use PDO;
use PDOException;
use InvalidArgumentException;
use RuntimeException;
use UnexpectedValueException;

/**
 * Describes a database source handler.
 */
interface DatabaseSourceInterface
{
    /**
     * Retrieve the database connector.
     *
     * @throws RuntimeException If the datahase was not set.
     * @return PDO
     */
    public function db();

    /**
     * Set the database's table to use.
     *
     * @param  string $table The source table.
     * @return DatabaseSourceInterface Returns the current source.
     */
    public function setTable($table);

    /**
     * Determine if a table is assigned.
     *
     * @return boolean
     */
    public function hasTable();

    /**
     * Get the database's current table.
     *
     * @throws RuntimeException If the table was not set.
     * @return string
     */
    public function table();

    /**
     * Create a table from a model's metadata.
     *
     * @return boolean TRUE if the table was created, otherwise FALSE.
     */
    public function createTable();

    /**
     * Alter an existing table to match the model's metadata.
     *
     * @return boolean TRUE if the table was altered, otherwise FALSE.
     */
    public function alterTable();

    /**
     * Determine if the source table exists.
     *
     * @return boolean TRUE if the table exists, otherwise FALSE.
     */
    public function tableExists();

    /**
     * Get the table columns information.
     *
     * @return array An associative array.
     */
    public function tableStructure();

    /**
     * Determine if the source table is empty or not.
     *
     * @return boolean TRUE if the table has no data, otherwise FALSE.
     */
    public function tableIsEmpty();
}
