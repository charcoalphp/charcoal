<?php

namespace Charcoal\Source;

use PDO;
use PDOException;
use InvalidArgumentException;
use RuntimeException;
use UnexpectedValueException;
// From 'charcoal-property'
use Charcoal\Property\PropertyField;
// From 'charcoal-core'
use Charcoal\Model\ModelInterface;
use Charcoal\Source\AbstractSource;
use Charcoal\Source\DatabaseSourceConfig;
use Charcoal\Source\DatabaseSourceInterface;
use Charcoal\Source\Database\DatabaseFilter;
use Charcoal\Source\Database\DatabaseOrder;
use Charcoal\Source\Database\DatabasePagination;
use Charcoal\Source\Expression;

/**
 * Database Source Handler, through PDO.
 */
class DatabaseSource extends AbstractSource implements
    DatabaseSourceInterface
{
    public const DEFAULT_DB_HOSTNAME = 'localhost';

    public const DEFAULT_TABLE_ALIAS = 'objTable';

    public const MYSQL_DRIVER_NAME   = 'mysql';
    public const SQLITE_DRIVER_NAME  = 'sqlite';

    /**
     * The database connector.
     *
     * @var PDO
     */
    private $pdo;

    /**
     * The {@see self::$model}'s table name.
     *
     * @var string
     */
    private $table;

    /**
     * Create a new database handler.
     *
     * @param array $data Class dependencies.
     */
    public function __construct(array $data)
    {
        $this->pdo = $data['pdo'];

        parent::__construct($data);
    }

    /**
     * Retrieve the database connector.
     *
     * @throws RuntimeException If the datahase was not set.
     * @return PDO
     */
    public function db()
    {
        if ($this->pdo === null) {
            throw new RuntimeException(sprintf(
                '[%s] Database connector was not set',
                $this->getModelClassForException()
            ));
        }
        return $this->pdo;
    }

    /**
     * Set the database's table to use.
     *
     * @param  string $table The source table.
     * @throws InvalidArgumentException If argument is not a string or alphanumeric/underscore.
     * @return self
     */
    public function setTable($table)
    {
        if (!is_string($table)) {
            throw new InvalidArgumentException(sprintf(
                '[%s] Database table name expects a string, received %s',
                $this->getModelClassForException(),
                gettype($table)
            ));
        }

        /**
         * For security reason, only alphanumeric characters (+ underscores)
         * are valid table names; Although SQL can support more,
         * there's really no reason to.
         */
        if (!preg_match('/[A-Za-z0-9_]/', $table)) {
            throw new InvalidArgumentException(sprintf(
                '[%s] Database table name "%s" is invalid: must be alphanumeric / underscore',
                $this->getModelClassForException(),
                $table
            ));
        }

        $this->table = $table;
        return $this;
    }

    /**
     * Determine if a table is assigned.
     *
     * @return boolean
     */
    public function hasTable()
    {
        return !empty($this->table);
    }

    /**
     * Get the database's current table.
     *
     * @throws RuntimeException If the table was not set.
     * @return string
     */
    public function table()
    {
        if ($this->table === null) {
            throw new RuntimeException(sprintf(
                '[%s] Database table name was not set',
                $this->getModelClassForException()
            ));
        }
        return $this->table;
    }

    /**
     * Create a table from a model's metadata.
     *
     * @return boolean TRUE if the table was created, otherwise FALSE.
     */
    public function createTable()
    {
        if ($this->tableExists() === true) {
            return true;
        }

        $dbh      = $this->db();
        $driver   = $dbh->getAttribute(PDO::ATTR_DRIVER_NAME);
        $model    = $this->model();
        $metadata = $model->metadata();

        $table   = $this->table();
        $fields  = $this->getModelFields($model);
        $columns = [];
        foreach ($fields as $field) {
            $fieldSql = $field->sql();
            if ($fieldSql) {
                $columns[] = $fieldSql;
            }
        }

        $query  = 'CREATE TABLE  `' . $table . '` (' . "\n";
        $query .= implode(',', $columns);

        $key = $model->key();
        if ($key) {
            if ($driver === self::SQLITE_DRIVER_NAME) {
                /** Convert MySQL syntax to SQLite */
                $query = preg_replace('/`' . $key . '` INT(EGER)? AUTO_INCREMENT/', '`' . $key . '` INTEGER PRIMARY KEY', $query, 1);
            } else {
                $query .= ', PRIMARY KEY (`' . $key . '`) ' . "\n";
            }
        }

        /** @todo Add indexes for all defined list constraints (yea... tough job...) */
        if ($driver === self::MYSQL_DRIVER_NAME) {
            $engine = 'InnoDB';
            $query .= ') ENGINE=' . $engine . ' DEFAULT CHARSET=utf8 COMMENT="' . addslashes($metadata['name']) . '";';
        } else {
            $query .= ');';
        }

        $this->logger->debug($query);
        $dbh->query($query);

        $this->setTableExists();

        return true;
    }

    /**
     * Alter an existing table to match the model's metadata.
     *
     * @return boolean TRUE if the table was altered, otherwise FALSE.
     */
    public function alterTable()
    {
        if ($this->tableExists() === false) {
            return false;
        }

        $dbh    = $this->db();
        $table  = $this->table();
        $fields = $this->getModelFields($this->model());
        $cols   = $this->tableStructure();
        foreach ($fields as $field) {
            $ident = $field->ident();

            if (!array_key_exists($ident, $cols)) {
                $fieldSql = $field->sql();
                if ($fieldSql) {
                    // The key does not exist at all.
                    $query = 'ALTER TABLE `' . $table . '` ADD ' . $fieldSql;
                    $this->logger->debug($query);
                    $dbh->query($query);
                } else {
                    $this->logger->warning('Empty column definition.', [
                        'table' => $table,
                        'field' => $ident,
                    ]);
                }
            } else {
                // The key exists. Validate.
                $col   = $cols[$ident];
                $alter = true;
                if (strtolower($col['Type']) !== strtolower($field->sqlType())) {
                    $alter = true;
                }

                if ((strtolower($col['Null']) !== 'no') !== $field->allowNull()) {
                    $alter = true;
                }

                if ($col['Default'] !== $field->defaultVal()) {
                    $alter = true;
                }

                if ($alter === true) {
                    $fieldSql = $field->sql();
                    if ($fieldSql) {
                        $query = 'ALTER TABLE `' . $table . '` CHANGE `' . $ident . '` ' . $fieldSql;
                        $this->logger->debug($query);
                        $dbh->query($query);
                    } else {
                        $this->logger->warning('Empty column definition.', [
                            'table' => $table,
                            'field' => $ident,
                        ]);
                    }
                }
            }
        }

        return true;
    }

    /**
     * Determine if the source table exists.
     *
     * @return boolean TRUE if the table exists, otherwise FALSE.
     */
    public function tableExists()
    {
        $dbh    = $this->db();
        $table  = $this->table();

        if (isset($dbh->tableExists, $dbh->tableExists[$table])) {
            return $dbh->tableExists[$table];
        }

        $exists = $this->performTableExists();
        $this->setTableExists($exists);

        return $exists;
    }

    /**
     * Perform a source table exists operation.
     *
     * @return boolean TRUE if the table exists, otherwise FALSE.
     */
    protected function performTableExists()
    {
        $dbh    = $this->db();
        $table  = $this->table();

        $driver = $dbh->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === self::SQLITE_DRIVER_NAME) {
            $query = sprintf('SELECT name FROM sqlite_master WHERE type = "table" AND name = "%s";', $table);
        } else {
            $query = sprintf('SHOW TABLES LIKE "%s"', $table);
        }

        $this->logger->debug($query);
        $sth    = $dbh->query($query);
        $exists = $sth->fetchColumn(0);

        return (bool)$exists;
    }

    /**
     * Store a reminder whether the source's database table exists.
     *
     * @param  boolean $exists Whether the table exists or not.
     * @return void
     */
    protected function setTableExists($exists = true)
    {
        $dbh   = $this->db();
        $table = $this->table();

        if (!isset($dbh->tableExists)) {
            $dbh->tableExists = [];
        }

        $dbh->tableExists[$table] = $exists;
    }

    /**
     * Get the table columns information.
     *
     * @return array An associative array.
     */
    public function tableStructure()
    {
        $dbh    = $this->db();
        $table  = $this->table();
        $driver = $dbh->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === self::SQLITE_DRIVER_NAME) {
            $query = sprintf('PRAGMA table_info("%s") ', $table);
        } else {
            $query = sprintf('SHOW COLUMNS FROM `%s`', $table);
        }

        $this->logger->debug($query);
        $sth = $dbh->query($query);

        $cols = $sth->fetchAll((PDO::FETCH_GROUP | PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC));
        if ($driver === self::SQLITE_DRIVER_NAME) {
            $struct = [];
            foreach ($cols as $col) {
                // Normalize SQLite's result (PRAGMA) with mysql's (SHOW COLUMNS)
                $struct[$col['name']] = [
                    'Type'      => $col['type'],
                    'Null'      => !!$col['notnull'] ? 'NO' : 'YES',
                    'Default'   => $col['dflt_value'],
                    'Key'       => !!$col['pk'] ? 'PRI' : '',
                    'Extra'     => '',
                ];
            }
            return $struct;
        } else {
            return $cols;
        }
    }

    /**
     * Determine if the source table is empty or not.
     *
     * @return boolean TRUE if the table has no data, otherwise FALSE.
     */
    public function tableIsEmpty()
    {
        $table = $this->table();
        $query = sprintf('SELECT NULL FROM `%s` LIMIT 1', $table);
        $this->logger->debug($query);
        $sth = $this->db()->query($query);
        return ($sth->rowCount() === 0);
    }

    /**
     * Retrieve all fields from a model.
     *
     * @todo   Move this method in StorableTrait or AbstractModel
     * @param  ModelInterface $model      The model to get fields from.
     * @param  array|null     $properties Optional list of properties to get.
     *     If NULL, retrieve all (from metadata).
     * @return PropertyField[]
     */
    private function getModelFields(ModelInterface $model, $properties = null)
    {
        if ($properties === null) {
            // No custom properties; use all (from model metadata)
            $properties = array_keys($model->metadata()->properties());
        } else {
            // Ensure the key is always in the required fields.
            $properties = array_unique(array_merge([ $model->key() ], $properties));
        }

        $fields = [];
        foreach ($properties as $propertyIdent) {
            $prop = $model->property($propertyIdent);
            if (!$prop || !$prop['active'] || !$prop['storable']) {
                continue;
            }

            $val = $model->propertyValue($propertyIdent);
            foreach ($prop->fields($val) as $fieldIdent => $field) {
                $fields[$field->ident()] = $field;
            }
        }

        return $fields;
    }

    /**
     * Load item by the primary column.
     *
     * @param  mixed             $ident Ident can be any scalar value.
     * @param  StorableInterface $item  Optional item to load into.
     * @return StorableInterface
     */
    public function loadItem($ident, StorableInterface $item = null)
    {
        $key = $this->model()->key();

        return $this->loadItemFromKey($key, $ident, $item);
    }

    /**
     * Load item by the given column.
     *
     * @param  string                 $key   Column name.
     * @param  mixed                  $ident Value of said column.
     * @param  StorableInterface|null $item  Optional. Item (storable object) to load into.
     * @throws \Exception If the query fails.
     * @return StorableInterface
     */
    public function loadItemFromKey($key, $ident, StorableInterface $item = null)
    {
        if ($item !== null) {
            $this->setModel($item);
        } else {
            $class = get_class($this->model());
            $item  = new $class();
        }

        // Strip invalid characters
        $key = preg_replace('/[^\w-]+/', '', $key);
        if (!$key) {
            return $item;
        }

        // Allow truthy values and zero
        if (!$ident && !is_numeric($ident)) {
            return $item;
        }

        $table = $this->table();
        $query = sprintf(
            'SELECT * FROM `%s` WHERE `%s` = :ident LIMIT 1',
            $table,
            $key
        );

        $binds = [
            'ident' => $ident
        ];

        return $this->loadItemFromQuery($query, $binds, $item);
    }

    /**
     * Load item by the given query statement.
     *
     * @param  string            $query The SQL SELECT statement.
     * @param  array             $binds Optional. The query parameters.
     * @param  StorableInterface $item  Optional. Item (storable object) to load into.
     * @throws PDOException If there is a query error.
     * @return StorableInterface
     */
    public function loadItemFromQuery($query, array $binds = [], StorableInterface $item = null)
    {
        if ($item !== null) {
            $this->setModel($item);
        } else {
            $class = get_class($this->model());
            $item = new $class();
        }

        // Missing parameters
        if (!$query) {
            return $item;
        }

        $sth = $this->dbQuery($query, $binds);
        if ($sth === false) {
            throw new PDOException(sprintf(
                '[%s] Could not load item',
                $this->getModelClassForException()
            ));
        }

        $data = $sth->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            $item->setFlatData($data);
        }

        return $item;
    }

    /**
     * Load items for the given model.
     *
     * @param  StorableInterface|null $item Optional model.
     * @return StorableInterface[]
     */
    public function loadItems(StorableInterface $item = null)
    {
        if ($item !== null) {
            $this->setModel($item);
        }

        $query = $this->sqlLoad();
        return $this->loadItemsFromQuery($query, [], $item);
    }

    /**
     * Load items for the given query statement.
     *
     * @param  string                 $query The SQL SELECT statement.
     * @param  array                  $binds This has to be done.
     * @param  StorableInterface|null $item  Model Item.
     * @return StorableInterface[]
     */
    public function loadItemsFromQuery($query, array $binds = [], StorableInterface $item = null)
    {
        if ($item !== null) {
            $this->setModel($item);
        }

        $items = [];

        $model = $this->model();
        $dbh   = $this->db();

        $this->logger->debug($query);
        $sth = $dbh->prepare($query);

        // @todo Binds
        if (!empty($binds)) {
            unset($binds);
        }

        $sth->execute();
        $sth->setFetchMode(PDO::FETCH_ASSOC);

        $className = get_class($model);
        while ($objData = $sth->fetch()) {
            $obj = new $className();
            $obj->setFlatData($objData);
            $items[] = $obj;
        }

        return $items;
    }

    /**
     * Save an item (create a new row) in storage.
     *
     * @param  StorableInterface $item The object to save.
     * @throws PDOException If a database error occurs.
     * @return mixed The created item ID, otherwise FALSE.
     */
    public function saveItem(StorableInterface $item)
    {
        if ($this->tableExists() === false) {
            /** @todo Optionnally turn off for some models */
            $this->createTable();
        }

        if ($item !== null) {
            $this->setModel($item);
        }
        $model  = $this->model();
        $table  = $this->table();
        $struct = array_keys($this->tableStructure());
        $fields = $this->getModelFields($model);

        $keys   = [];
        $values = [];
        $binds  = [];
        $types  = [];
        foreach ($fields as $field) {
            $key = $field->ident();
            if (in_array($key, $struct)) {
                $keys[]      = '`' . $key . '`';
                $values[]    = ':' . $key . '';
                $binds[$key] = $field->val();
                $types[$key] = $field->sqlPdoType();
            }
        }

        $query = '
            INSERT
                INTO
            `' . $table . '`
                (' . implode(', ', $keys) . ')
            VALUES
                (' . implode(', ', $values) . ')';

        $result = $this->dbQuery($query, $binds, $types);

        if ($result === false) {
            throw new PDOException(sprintf(
                '[%s] Could not save item',
                $this->getModelClassForException()
            ));
        } else {
            if ($model->id()) {
                return $model->id();
            } else {
                return $this->db()->lastInsertId();
            }
        }
    }

    /**
     * Update an item in storage.
     *
     * @param  StorableInterface $item       The object to update.
     * @param  array             $properties The list of properties to update, if not all.
     * @return boolean TRUE if the item was updated, otherwise FALSE.
     */
    public function updateItem(StorableInterface $item, array $properties = null)
    {
        if ($item !== null) {
            $this->setModel($item);
        }
        $model  = $this->model();
        $table  = $this->table();
        $struct = array_keys($this->tableStructure());
        $fields = $this->getModelFields($model, $properties);

        $updates = [];
        $binds   = [];
        $types   = [];
        foreach ($fields as $field) {
            $key = $field->ident();
            if (in_array($key, $struct)) {
                if ($key !== $model->key()) {
                    $param = ':' . $key;
                    $updates[] = '`' . $key . '` = ' . $param;
                }
                $binds[$key] = $field->val();
                $types[$key] = $field->sqlPdoType();
            } else {
                $this->logger->warning(
                    sprintf('Field "%s" not in table structure', $key),
                    [
                        'model' => get_class($model),
                        'table' => $table,
                        'field' => $key,
                    ]
                );
            }
        }

        if (empty($updates)) {
            $this->logger->warning(
                'Could not update items. No valid fields were set or available in database table.',
                [
                    'model'      => get_class($model),
                    'table'      => $table,
                    'properties' => $properties,
                    'structure'  => $struct
                ]
            );
            return false;
        }

        $binds[$model->key()] = $model->id();
        $types[$model->key()] = PDO::PARAM_STR;

        $query = '
            UPDATE
                `' . $table . '`
            SET
                ' . implode(", \n\t", $updates) . '
            WHERE
                `' . $model->key() . '`=:' . $model->key() . '';

        $driver = $this->db()->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver == self::MYSQL_DRIVER_NAME) {
            $query .= "\n" . 'LIMIT 1';
        }

        $result = $this->dbQuery($query, $binds, $types);

        if ($result === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Delete an item from storage.
     *
     * @param  StorableInterface $item Optional item to delete. If none, the current model object will be used.
     * @throws UnexpectedValueException If the item does not have an ID.
     * @return boolean TRUE if the item was deleted, otherwise FALSE.
     */
    public function deleteItem(StorableInterface $item = null)
    {
        if ($item !== null) {
            $this->setModel($item);
        }

        $model = $this->model();

        if (!$model->id()) {
            throw new UnexpectedValueException(sprintf(
                '[%s] Can not delete item; no ID',
                $this->getModelClassForException()
            ));
        }

        $key   = $model->key();
        $table = $this->table();
        $query = '
            DELETE FROM
                `' . $table . '`
            WHERE
                `' . $key . '` = :id';

        $driver = $this->db()->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver == self::MYSQL_DRIVER_NAME) {
            $query .= "\n" . 'LIMIT 1';
        }

        $binds = [
            'id' => $model->id()
        ];

        $result = $this->dbQuery($query, $binds);

        if ($result === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Execute a SQL query, with PDO, and returns the PDOStatement.
     *
     * If the query fails, this method will return false.
     *
     * @param  string $query The SQL query to executed.
     * @param  array  $binds Optional. Query parameter binds.
     * @param  array  $types Optional. Types of parameter bindings.
     * @throws PDOException If the SQL query fails.
     * @return \PDOStatement|false The PDOStatement, otherwise FALSE.
     */
    public function dbQuery($query, array $binds = [], array $types = [])
    {
        $this->logger->debug($query, $binds);

        $sth = $this->dbPrepare($query, $binds, $types);
        if ($sth === false) {
            return false;
        }

        try {
            $result = $sth->execute();
        } catch (PDOException $e) {
            throw new PDOException(sprintf(
                '[%s] Failed SQL query: ' . $e->getMessage(),
                $this->getModelClassForException()
            ), 0, $e);
        }

        if ($result === false) {
            return false;
        }

        return $sth;
    }

    /**
     * Prepare an SQL query, with PDO, and return the PDOStatement.
     *
     * If the preparation fails, this method will return false.
     *
     * @param  string $query The SQL query to executed.
     * @param  array  $binds Optional. Query parameter binds.
     * @param  array  $types Optional. Types of parameter bindings.
     * @return \PDOStatement|false The PDOStatement, otherwise FALSE.
     */
    public function dbPrepare($query, array $binds = [], array $types = [])
    {
        $sth = $this->db()->prepare($query);
        if (!$sth) {
            return false;
        }

        if (!empty($binds)) {
            foreach ($binds as $key => $val) {
                if ($binds[$key] === null) {
                    $types[$key] = PDO::PARAM_NULL;
                } elseif (!is_scalar($binds[$key])) {
                    $binds[$key] = json_encode($binds[$key]);
                }
                $type  = (isset($types[$key]) ? $types[$key] : PDO::PARAM_STR);
                $param = ':' . $key;
                $sth->bindParam($param, $binds[$key], $type);
            }
        }

        return $sth;
    }

    /**
     * Compile the SELECT statement for fetching one or more objects.
     *
     * @throws UnexpectedValueException If the source does not have a table defined.
     * @return string
     */
    public function sqlLoad()
    {
        if (!$this->hasTable()) {
            throw new UnexpectedValueException(sprintf(
                '[%s] Can not get SQL SELECT clause; no databse table name defined',
                $this->getModelClassForException()
            ));
        }

        $selects = $this->sqlSelect();
        $tables  = $this->sqlFrom();
        $filters = $this->sqlFilters();
        $orders  = $this->sqlOrders();
        $limits  = $this->sqlPagination();

        $query = 'SELECT ' . $selects . ' FROM ' . $tables . $filters . $orders . $limits;
        return $query;
    }

    /**
     * Compile the SELECT statement for fetching the number of objects.
     *
     * @throws UnexpectedValueException If the source does not have a table defined.
     * @return string
     */
    public function sqlLoadCount()
    {
        if (!$this->hasTable()) {
            throw new UnexpectedValueException(sprintf(
                '[%s] Can not get SQL count; no databse table name defined',
                $this->getModelClassForException()
            ));
        }

        $tables  = $this->sqlFrom();
        $filters = $this->sqlFilters();

        $query = 'SELECT COUNT(*) FROM ' . $tables . $filters;
        return $query;
    }

    /**
     * Compile the SELECT clause.
     *
     * @throws UnexpectedValueException If the clause has no selectable fields.
     * @return string
     */
    public function sqlSelect()
    {
        $properties = $this->properties();
        if (empty($properties)) {
            return self::DEFAULT_TABLE_ALIAS . '.*';
        }

        $parts = [];
        foreach ($properties as $key) {
            $parts[] = Expression::quoteIdentifier($key, self::DEFAULT_TABLE_ALIAS);
        }

        if (empty($parts)) {
            throw new UnexpectedValueException(sprintf(
                '[%s] Can not get SQL SELECT clause; no valid properties',
                $this->getModelClassForException()
            ));
        }

        $clause = implode(', ', $parts);

        return $clause;
    }

    /**
     * Compile the FROM clause.
     *
     * @throws UnexpectedValueException If the source does not have a table defined.
     * @return string
     */
    public function sqlFrom()
    {
        if (!$this->hasTable()) {
            throw new UnexpectedValueException(sprintf(
                '[%s] Can not get SQL FROM clause; no database table name defined',
                $this->getModelClassForException()
            ));
        }

        $table = $this->table();
        return '`' . $table . '` AS `' . self::DEFAULT_TABLE_ALIAS . '`';
    }

    /**
     * Compile the WHERE clause.
     *
     * @todo   [2016-02-19] Use bindings for filters value
     * @return string
     */
    public function sqlFilters()
    {
        if (!$this->hasFilters()) {
            return '';
        }

        $criteria = $this->createFilter([
            'filters' => $this->filters()
        ]);

        $sql = $criteria->sql();
        if ($sql && strlen($sql) > 0) {
            $sql = ' WHERE ' . $sql;
        }

        return $sql;
    }

    /**
     * Compile the ORDER BY clause.
     *
     * @return string
     */
    public function sqlOrders()
    {
        if (!$this->hasOrders()) {
            return '';
        }

        $parts = [];
        foreach ($this->orders() as $order) {
            if (!$order instanceof DatabaseOrder) {
                $order = $this->createOrder($order->data());
            }

            $sql = $order->sql();
            if ($sql && strlen($sql) > 0) {
                $parts[] = $sql;
            }
        }

        if (empty($parts)) {
            return '';
        }

        return ' ORDER BY ' . implode(', ', $parts);
    }

    /**
     * Compile the LIMIT clause.
     *
     * @return string
     */
    public function sqlPagination()
    {
        $pager = $this->pagination();
        if (!$pager instanceof DatabasePagination) {
            $pager = $this->createPagination($pager->data());
        }

        $sql = $pager->sql();
        if ($sql && strlen($sql) > 0) {
            $sql = ' ' . $sql;
        }

        return $sql;
    }

    /**
     * Create a new filter expression.
     *
     * @param  array $data Optional expression data.
     * @return DatabaseFilter
     */
    protected function createFilter(array $data = null)
    {
        $filter = new DatabaseFilter();
        if ($data !== null) {
            $filter->setData($data);
        }
        return $filter;
    }

    /**
     * Create a new order expression.
     *
     * @param  array $data Optional expression data.
     * @return DatabaseOrder
     */
    protected function createOrder(array $data = null)
    {
        $order = new DatabaseOrder();
        if ($data !== null) {
            $order->setData($data);
        }
        return $order;
    }

    /**
     * Create a new pagination clause.
     *
     * @param  array $data Optional clause data.
     * @return DatabasePagination
     */
    protected function createPagination(array $data = null)
    {
        $pagination = new DatabasePagination();
        if ($data !== null) {
            $pagination->setData($data);
        }
        return $pagination;
    }

    /**
     * Create a new database source config.
     *
     * @see    \Charcoal\Config\ConfigurableTrait
     * @param  array $data Optional data.
     * @return DatabaseSourceConfig
     */
    public function createConfig(array $data = null)
    {
        $config = new DatabaseSourceConfig($data);
        return $config;
    }
}
