<?php

namespace Charcoal\Source;

/**
*
*/
interface DatabaseSourceInterface
{
    /**
    * @param string $databaseIdent The database identifiter.
    * @return DatabaseSource Chainable
    */
    public function setDatabaseIdent($databaseIdent);

    /**
    * Get the current database ident.
    * If null, then the project's default (from `Charcoal::config()` will be used.)
    *
    * @return string
    */
    public function databaseIdent();

    /**
    * @param  array $databaseConfig The database confguration.
    * @return DatabaseSource Chainable
    */
    public function setDatabaseConfig(array $databaseConfig);

    /**
    * @return mixed
    */
    public function databaseConfig();

    /**
    * Set the database's table to use.
    *
    * @param string $table The database table.
    * @return DatabaseSource Chainable
    */
    public function setTable($table);

    /**
    * Get the database's current table.
    *
    * @return string
    */
    public function table();

    /**
    * Create a table from a model's metadata.
    *
    * @return boolean Success / Failure
    */
    public function createTable();

    /**
    * Alter an existing table to match the model's metadata.
    *
    * @return boolean Success / Failure
    */
    public function alterTable();

    /**
    * @return boolean
    */
    public function tableExists();

    /**
    * Get the table columns information.
    *
    * @return array
    */
    public function tableStructure();

    /**
    * Check wether the source table is empty (`true`) or not (`false`)
    *
    * @return boolean
    */
    public function tableIsEmpty();

    /**
    * @param string $databaseIdent The database identifier to return. If null, use default.
    * @return \PDO
    */
    public function db($databaseIdent = null);
}
