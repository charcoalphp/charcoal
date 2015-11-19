<?php

namespace Charcoal\Source;

/**
*
*/
interface DatabaseSourceInterface
{
    /**
    * @param string $database_ident
    * @throws InvalidArgumentException if ident is not a string
    * @return DatabaseSource Chainable
    */
    public function set_database_ident($database_ident);

    /**
    * Get the current database ident.
    * If null, then the project's default (from `Charcoal::config()` will be used.)
    *
    * @return string
    */
    public function database_ident();

    /**
    * @param array $database_config
    * @throws InvalidArgumentException
    * @return DatabaseSource Chainable
    */
    public function set_database_config($database_config);

    /**
    * @return mixed
    */
    public function database_config();

    /**
    * Set the database's table to use.
    *
    * @param string $table
    * @throws InvalidArgumentException if argument is not a string
    * @return DatabaseSource Chainable
    */
    public function set_table($table);

    /**
    * Get the database's current table.
    *
    * @throws Exception if the table was not set
    * @return string
    */
    public function table();

    /**
    * Create a table from a model's metadata.
    *
    * @return boolean Success / Failure
    */
    public function create_table();

    /**
    * Alter an existing table to match the model's metadata.
    *
    * @return boolean Success / Failure
    */
    public function alter_table();

    /**
    * @return boolean
    */
    public function table_exists();

    /**
    * Get the table columns information.
    *
    * @return array
    */
    public function table_structure();

    /**
    * Check wether the source table is empty (`true`) or not (`false`)
    *
    * @return boolean
    */
    public function table_is_empty();

    /**
    * @param string $database_ident
    * @throws Exception if the database is not set.
    * @return PDO
    */
    public function db($database_ident = null);
}
