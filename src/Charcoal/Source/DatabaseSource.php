<?php

namespace Charcoal\Source;

// Dependencies from `PHP`
use \Exception;
use \InvalidArgumentException;

// Dependencies from `PHP` modules
use \PDO;
use \PDOException;

use \Charcoal\Config\ConfigInterface;

// Intra-module (`charcoal-core`) dependencies
use \Charcoal\Model\ModelInterface;

// Local namespace dependencies
use \Charcoal\Source\AbstractSource;
use \Charcoal\Source\DatabaseSourceConfig;
use \Charcoal\Source\DatabaseSourceInterface;
use \Charcoal\Source\Database\DatabaseFilter;
use \Charcoal\Source\Database\DatabaseOrder;
use \Charcoal\Source\Database\DatabasePagination;

/**
 * Database Source, through PDO.
 */
class DatabaseSource extends AbstractSource implements DatabaseSourceInterface
{
    const DEFAULT_DB_HOSTNAME = 'localhost';
    const DEFAULT_DB_TYPE = 'mysql';

    /**
     * @var PDO
     */
    private $pdo;

    /**
     * @var string $table
     */
    private $table = null;

    /**
     * @var array $dbs
     */
    private static $db;

    /**
     * @param array $data Class dependencies.
     */
    public function __construct(array $data)
    {
        $this->pdo = $data['pdo'];

        parent::__construct($data);
    }

    /**
     * Set the database's table to use.
     *
     * @param string $table The source table.
     * @throws InvalidArgumentException If argument is not a string or alphanumeric/underscore.
     * @return DatabaseSource Chainable
     */
    public function setTable($table)
    {
        if (!is_string($table)) {
            throw new InvalidArgumentException(
                sprintf(
                    'DatabaseSource::setTable() expects a string as table. (%s given). [%s]',
                    gettype($table),
                    get_class($this->model())
                )
            );
        }
        // For security reason, only alphanumeric characters (+ underscores) are valid table names.
        // Although SQL can support more, there's really no reason to.
        if (!preg_match('/[A-Za-z0-9_]/', $table)) {
            throw new InvalidArgumentException(
                sprintf('Table name "%s" is invalid: must be alphanumeric / underscore.', $table)
            );
        }
        $this->table = $table;

        return $this;
    }

    /**
     * Get the database's current table.
     *
     * @throws Exception If the table was not set.
     * @return string
     */
    public function table()
    {
        if ($this->table === null) {
            throw new Exception(
                'Table was not set.'
            );
        }
        return $this->table;
    }

    /**
     * Create a table from a model's metadata.
     *
     * @return boolean Success / Failure
     */
    public function createTable()
    {
        if ($this->tableExists()) {
            // Table already exists
            return true;
        }

        $dbDriver = $this->db()->getAttribute(PDO::ATTR_DRIVER_NAME);

        $model = $this->model();
        $metadata = $model->metadata();
        $fields = $this->getModelFields($model);
        $fieldsSql = [];
        foreach ($fields as $field) {
            $fieldsSql[] = $field->sql();
        }

        $q = 'CREATE TABLE  `'.$this->table().'` ('."\n";
        $q .= implode(',', $fieldsSql);
        $key = $model->key();
        if ($key) {
            $q .= ', PRIMARY KEY (`'.$key.'`) '."\n";
        }
        /** @todo add indexes for all defined list constraints (yea... tough job...) */
        if ($dbDriver === 'mysql') {
            $engine = 'InnoDB';
            $q .= ') ENGINE='.$engine.' DEFAULT CHARSET=utf8 COMMENT=\''.addslashes($metadata['name']).'\';';
        } else {
            $q .= ');';
        }
        $this->logger->debug($q);
        $this->db()->query($q);

        return true;
    }

    /**
     * Alter an existing table to match the model's metadata.
     *
     * @return boolean Success / Failure
     */
    public function alterTable()
    {
        if (!$this->tableExists()) {
            return false;
        }

        $fields = $this->getModelFields($this->model());

        $cols = $this->tableStructure();

        foreach ($fields as $field) {
            $ident = $field->ident();

            if (!array_key_exists($ident, $cols)) {
                // The key does not exist at all.
                $q = 'ALTER TABLE `'.$this->table().'` ADD '.$field->sql();
                $this->logger->debug($q);
                $this->db()->query($q);
            } else {
                // The key exists. Validate.
                $col = $cols[$ident];
                $alter = true;
                if (strtolower($col['Type']) != strtolower($field->sqlType())) {
                    $alter = true;
                }
                if ((strtolower($col['Null']) == 'no') && !$field->allowNull()) {
                    $alter = true;
                }
                if ((strtolower($col['Null']) != 'no') && $field->allowNull()) {
                    $alter = true;
                }
                if ($col['Default'] != $field->defaultVal()) {
                    $alter = true;
                }

                if ($alter === true) {
                    $q = 'ALTER TABLE `'.$this->table().'` CHANGE `'.$ident.'` '.$field->sql();
                    $this->logger->debug($q);
                    $this->db()->query($q);
                }
            }
        }

        return true;
    }

    /**
     * @return boolean
     */
    public function tableExists()
    {
        $dbDriver = $this->db()->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($dbDriver === 'sqlite') {
            $q = 'SELECT name FROM sqlite_master WHERE type=\'table\' AND name=\''.$this->table().'\';';
        } else {
            $q = 'SHOW TABLES LIKE \''.$this->table().'\'';
        }
        $this->logger->debug($q);
        $res = $this->db()->query($q);
        $tableExists = $res->fetchColumn(0);

        // Return as boolean
        return !!$tableExists;
    }

    /**
     * Get the table columns information.
     *
     * @return array
     */
    public function tableStructure()
    {
        $dbDriver = $this->db()->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($dbDriver === 'sqlite') {
            $q = 'PRAGMA table_info(\''.$this->table().'\') ';
        } else {
            $q = 'SHOW COLUMNS FROM `'.$this->table().'`';
        }
        $this->logger->debug($q);
        $res = $this->db()->query($q);
        $cols = $res->fetchAll((PDO::FETCH_GROUP|PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC));
        if ($dbDriver === 'sqlite') {
            $ret = [];
            foreach ($cols as $c) {
                // Normalize SQLite's result (PRAGMA) with mysql's (SHOW COLUMNS)
                $ret[$c['name']] = [
                    'Type'      => $c['type'],
                    'Null'      => !!$c['notnull'] ? 'NO' : 'YES',
                    'Default'   => $c['dflt_value'],
                    'Key'       => !!$c['pk'] ? 'PRI' : '',
                    'Extra'     => ''
                ];
            }
            return $ret;
        } else {
            return $cols;
        }
    }

    /**
     * Check wether the source table is empty (`true`) or not (`false`)
     *
     * @return boolean
     */
    public function tableIsEmpty()
    {
        $q = 'SELECT NULL FROM `'.$this->table().'` LIMIT 1';
        $this->logger->debug($q);
        $res = $this->db()->query($q);
        return ($res->rowCount() === 0);
    }

    /**
     * @throws Exception If the database can not set.
     * @return PDO
     */
    public function db()
    {
        return $this->pdo;
    }

    /**
     * Get all the fields of a model.
     *
     * @param ModelInterface $model      The model to get fields from.
     * @param array|null     $properties Optional list of properties to get. If null, retrieve all (from metadata).
     * @return array
     * @todo Move this method in StorableTrait or AbstractModel
     */
    private function getModelFields(ModelInterface $model, $properties = null)
    {
        if ($properties === null) {
            // No custom properties; use all (from model metadata)
            $properties = array_keys($model->metadata()->properties());
        } else {
            // Ensure the key is always in the required fields.
            $properties = array_merge($properties, [ $model->key() ]);
        }

        $fields = [];
        foreach ($properties as $propertyIdent) {
            $p = $model->p($propertyIdent);
            $v = $model->propertyValue($propertyIdent);
            if (!$p || !$p->active()) {
                continue;
            }
            foreach ($p->fields($v) as $fieldIdent => $field) {
                $fields[$field->ident()] = $field;
            }
        }
        return $fields;
    }

    /**
     * @param mixed             $ident Ident can be any scalar value.
     * @param StorableInterface $item  Optional item to load into.
     * @return StorableInterface
     */
    public function loadItem($ident, StorableInterface $item = null)
    {
        $key = $this->model()->key();

        return $this->loadItemFromKey($key, $ident, $item);
    }

    /**
     * Load item from a custom column's name ($key)
     *
     * @param  string                 $key   Column name.
     * @param  mixed                  $ident Value of said column.
     * @param  StorableInterface|null $item  Optional. Item (storable object) to load into.
     * @throws Exception If the query fails.
     * @return StorableInterface             Item
     */
    public function loadItemFromKey($key, $ident, StorableInterface $item = null)
    {
        if ($item !== null) {
            $this->setModel($item);
        } else {
            $class = get_class($this->model());
            $item = new $class;
        }

        // Missing parameters
        if (!$key || !$ident) {
            return $item;
        }

        $q = '
            SELECT
                *
            FROM
               `'.$this->table().'`
            WHERE
               `'.$key.'`=:ident
            LIMIT
               1';

        $binds = [
            'ident' => $ident
        ];

        return $this->loadItemFromQuery($q, $binds, $item);
    }

    /**
     * @param string            $query The SQL query.
     * @param array             $binds Optional. The query parameters.
     * @param StorableInterface $item  Optional. Item (storable object) to load into.
     * @throws Exception If there is a query error.
     * @return StorableInterface Item.
     */
    public function loadItemFromQuery($query, array $binds = [], StorableInterface $item = null)
    {
        if ($item !== null) {
            $this->setModel($item);
        } else {
            $class = get_class($this->model());
            $item = new $class;
        }

        // Missing parameters
        if (!$query) {
            return $item;
        }

        $sth = $this->dbQuery($query, $binds);
        if ($sth === false) {
            throw new Exception('Error');
        }

        $data = $sth->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            $item->setFlatData($data);
        }

        return $item;
    }

    /**
     * @param StorableInterface|null $item Optional item to use as model.
     * @see this->loadItemsFromQuery()
     * @return array
     */
    public function loadItems(StorableInterface $item = null)
    {
        if ($item !== null) {
            $this->setModel($item);
        }

        $q = $this->sqlLoad();
        return $this->loadItemsFromQuery($q, [], $item);
    }

    /**
     * Loads items to a list from a query
     * Allows external use.
     *
     * @param  string                 $q     The actual query.
     * @param  array                  $binds This has to be done.
     * @param  StorableInterface|null $item  Model Item.
     * @return array                         Collection of Items | Model
     */
    public function loadItemsFromQuery($q, array $binds = [], StorableInterface $item = null)
    {
        if ($item !== null) {
            $this->setModel($item);
        }

        // Out
        $items = [];

        $model = $this->model();
        $db = $this->db();

        $this->logger->debug($q);
        $sth = $db->prepare($q);

        // @todo Binds
        if (!empty($binds)) {
            //
            unset($binds);
        }

        $sth->execute();
        $sth->setFetchMode(PDO::FETCH_ASSOC);

        $classname = get_class($model);
        while ($objData = $sth->fetch()) {
            $obj = new $classname;
            $obj->setFlatData($objData);
            $items[] = $obj;
        }

        return $items;
    }

    /**
     * Save an item (create a new row) in storage.
     *
     * @param StorableInterface $item The object to save.
     * @throws Exception If a database error occurs.
     * @return mixed The created item ID, or false in case of an error.
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
        $model = $this->model();

        $tableStructure = array_keys($this->tableStructure());

        $fields = $this->getModelFields($model);

        $keys   = [];
        $values = [];
        $binds  = [];
        $binds_types = [];
        foreach ($fields as $f) {
            $k = $f->ident();
            if (in_array($k, $tableStructure)) {
                $keys[]    = '`'.$k.'`';
                $values[]  = ':'.$k.'';
                $binds[$k] = $f->val();
                $binds_types[$k] = $f->sqlPdoType();
            }
        }

        $q = '
            INSERT
                INTO
            `'.$this->table().'`
                ('.implode(', ', $keys).')
            VALUES
                ('.implode(', ', $values).')';

        $res = $this->dbQuery($q, $binds, $binds_types);

        if ($res === false) {
            throw new Exception(
                'Could not save item.'
            );
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
     * @param StorableInterface $item       The object to update.
     * @param array             $properties The list of properties to update, if not all.
     * @return boolean Success / Failure
     */
    public function updateItem(StorableInterface $item, array $properties = null)
    {
        if ($item !== null) {
            $this->setModel($item);
        }
        $model = $this->model();

        $tableStructure = array_keys($this->tableStructure());
        $fields = $this->getModelFields($model, $properties);

        $updates = [];
        $binds   = [];
        $binds_types = [];
        foreach ($fields as $f) {
            $k = $f->ident();
            if (in_array($k, $tableStructure)) {
                if ($k !== $model->key()) {
                    $updates[] = '`'.$k.'` = :'.$k;
                }
                $binds[$k] = $f->val();
                $binds_types[$k] = $f->sqlPdoType();
            } else {
                $this->logger->debug(
                    sprintf('Field %s not in table structure', $k)
                );
            }
        }
        if (empty($updates)) {
            $this->logger->warning('Could not update items. No valid fields were set / available in database table.', [
                'properties'    => $properties,
                'structure'     => $tableStructure
            ]);
            return false;
        }

        $binds[$model->key()] = $model->id();
        $binds_types[$model->key()] = PDO::PARAM_STR;

        $q = '
            UPDATE
                `'.$this->table().'`
            SET
                '.implode(", \n\t", $updates).'
            WHERE
                `'.$model->key().'`=:'.$model->key().'
            LIMIT
                1';

        $res = $this->dbQuery($q, $binds, $binds_types);

        if ($res === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Delete an item from storage
     *
     * @param StorableInterface $item Optional item to delete. If none, the current model object will be used.
     * @throws Exception If the item does not have an ID.
     * @return boolean Success / Failure
     */
    public function deleteItem(StorableInterface $item = null)
    {
        if ($item !== null) {
            $this->setModel($item);
        }
        $model = $this->model();

        if (!$model->id()) {
            throw new Exception(
                sprintf('Can not delete "%s" item. No ID.', get_class($this))
            );
        }

        $q = '
            DELETE FROM
                `'.$this->table().'`
            WHERE
                `'.$model->key().'` = :id
            LIMIT
                1';

        $binds = [
            'id' => $model->id()
        ];

        $res = $this->dbQuery($q, $binds);

        if ($res === false) {
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
     * @param string $q           The SQL query to executed.
     * @param array  $binds       Optional. Query parameter binds.
     * @param array  $binds_types Optional. Types of parameter bindings.
     * @return PDOStatement|false The PDOStatement, or false in case of error
     */
    public function dbQuery($q, array $binds = [], array $binds_types = [])
    {
        $this->logger->debug($q, $binds);
        $sth = $this->db()->prepare($q);
        if (!empty($binds)) {
            foreach ($binds as $k => $v) {
                if ($binds[$k] === null) {
                    $binds_types[$k] = PDO::PARAM_NULL;
                } elseif (!is_scalar($binds[$k])) {
                    $binds[$k] = json_encode($binds[$k]);
                }
                $type = (isset($binds_types[$k]) ? $binds_types[$k] : PDO::PARAM_STR);
                $sth->bindParam(':'.$k, $binds[$k], $type);
            }
        }

        $ret = $sth->execute();
        if ($ret === false) {
            return false;
        }

        return $sth;
    }

    /**
     * @throws Exception If the source does not have a table defined.
     * @return string
     */
    public function sqlLoad()
    {
        $table = $this->table();
        if (!$table) {
            throw new Exception(
                'Can not get SQL. No table defined.'
            );
        }

        $selects = $this->sqlSelect();
        $tables  = '`'.$table.'` AS objTable';
        $filters = $this->sqlFilters();
        $orders  = $this->sqlOrders();
        $limits  = $this->sqlPagination();

        $q = 'SELECT '.$selects.' FROM '.$tables.$filters.$orders.$limits;
        return $q;
    }

    /**
     * Get a special SQL query for loading the count.
     *
     * @throws Exception If the source does not have a table defined.
     * @return string
     */
    public function sqlLoadCount()
    {
        $table = $this->table();
        if (!$table) {
            throw new Exception(
                'Can not get count SQL. No table defined.s'
            );
        }

        $tables = '`'.$table.'` AS objTable';
        $filters = $this->sqlFilters();
        $q = 'SELECT COUNT(*) FROM '.$tables.$filters;
        return $q;
    }

    /**
     * @return string
     */
    public function sqlSelect()
    {
        $properties = $this->properties();
        if (empty($properties)) {
            return 'objTable.*';
        }

        $sql = '';
        $propsSql = [];
        foreach ($properties as $p) {
            $propsSql[] = 'objTable.`'.$p.'`';
        }
        if (!empty($propsSql)) {
            $sql = implode(', ', $propsSql);
        }

        return $sql;
    }

    /**
     * @return string
     * @todo 2016-02-19 Use bindings for filters value
     */
    public function sqlFilters()
    {
        $sql = '';

        $filters = $this->filters();
        if (empty($filters)) {
            return '';
        }

        // Process filters
        $filtersSql = [];
        foreach ($filters as $f) {
            $fSql = $f->sql();
            if ($fSql) {
                $filtersSql[] = [
                    'sql'     => $f->sql(),
                    'operand' => $f->operand()
                ];
            }
        }
        if (empty($filtersSql)) {
            return '';
        }

        $sql .= ' WHERE';
        $i = 0;

        foreach ($filtersSql as $f) {
            if ($i > 0) {
                $sql .= ' '.$f['operand'];
            }
            $sql .= ' '.$f['sql'];
            $i++;
        }
        return $sql;
    }

    /**
     * @return string
     */
    public function sqlOrders()
    {
        $sql = '';

        if (!empty($this->orders)) {
            $ordersSql = [];
            foreach ($this->orders as $o) {
                $ordersSql[] = $o->sql();
            }
            if (!empty($ordersSql)) {
                $sql = ' ORDER BY '.implode(', ', $ordersSql);
            }
        }

        return $sql;
    }

    /**
     * @return string
     */
    public function sqlPagination()
    {
        return $this->pagination()->sql();
    }

    /**
     * @return FilterInterface
     */
    protected function createFilter()
    {
        $filter = new DatabaseFilter();
        return $filter;
    }

    /**
     * @return OrderInterface
     */
    protected function createOrder()
    {
        $order = new DatabaseOrder();
        return $order;
    }

    /**
     * @return PaginationInterface
     */
    protected function createPagination()
    {
        $pagination = new DatabasePagination();
        return $pagination;
    }

    /**
     * ConfigurableTrait > createConfig()
     *
     * Overrides the method defined in AbstractSource to returns a `DatabaseSourceConfig` object.
     *
     * @param array $data Optional.
     * @return DatabaseSourceConfig
     */
    public function createConfig(array $data = null)
    {
        $config = new DatabaseSourceConfig($data);
        return $config;
    }
}
