<?php

namespace Charcoal\Source;

// Dependencies from `PHP`
use \Exception;
use \InvalidArgumentException;

// Dependencies from `PHP` modules
use \PDO;
use \PDOException;

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
    * @var string $databaseIdent
    */
    private $databaseIdent;
    /**
    * @var DatabaseSourceConfig $databaseConfig
    */
    private $databaseConfig;

    /**
    * @var string $table
    */
    private $table = null;

    /**
    * @var array $dbs
    */
    private static $dbs = [];

    /**
    * @param string $databaseIdent
    * @throws InvalidArgumentException if ident is not a string
    * @return DatabaseSource Chainable
    */
    public function setDatabaseIdent($databaseIdent)
    {
        if (!is_string($databaseIdent)) {
            throw new InvalidArgumentException(
                'setDatabase() expects a string as database ident.'
            );
        }
        $this->databaseIdent = $databaseIdent;
        return $this;
    }

    /**
    * Get the current database ident.
    * If null, then the project's default (from `Charcoal::config()` will be used.)
    *
    * @return string
    */
    public function databaseIdent()
    {
        if ($this->databaseIdent === null) {
            $container = \Charcoal\App\App::instance()->getContainer();
            $appConfig = $container['config'];
            return $appConfig['default_database'];
        }
        return $this->databaseIdent;
    }

    /**
    * @param array $databaseConfig
    * @throws InvalidArgumentException
    * @return DatabaseSource Chainable
    */
    public function setDatabaseConfig($databaseConfig)
    {
        if (!is_array($databaseConfig)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Database config needs to be an array. (%s given) [%s]',
                    gettype($databaseConfig),
                    get_class($this->model())
                )
            );
        }
        $this->databaseConfig = $databaseConfig;
        return $this;
    }

    /**
    * @return mixed
    */
    public function databaseConfig()
    {
        if ($this->databaseConfig === null) {
            $ident = $this->databaseIdent();
            $container = \Charcoal\App\App::instance()->getContainer();
            $appConfig = $container['config'];
            $default = $appConfig->defaultDatabase();
            return $appConfig->databaseConfig($default);
        }
        return $this->databaseConfig;
    }

    /**
    * Set the database's table to use.
    *
    * @param string $table
    * @throws InvalidArgumentException if argument is not a string
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
        $this->table = $table;

        return $this;
    }

    /**
    * Get the database's current table.
    *
    * @throws Exception if the table was not set
    * @return string
    */
    public function table()
    {
        if ($this->table === null) {
            throw new Exception('Table was not set.');
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
        $q .= ') ENGINE=MYISAM DEFAULT CHARSET=utf8 COMMENT=\''.addslashes($metadata['name']).'\';';
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
                $res = $this->db()->query($q);
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
        $q = 'SHOW TABLES LIKE \''.$this->table().'\'';
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
        $q = 'SHOW COLUMNS FROM `'.$this->table().'`';
        $this->logger->debug($q);
        $res = $this->db()->query($q);
        $cols = $res->fetchAll((PDO::FETCH_GROUP|PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC));
        return $cols;
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
    * @param string $databaseIdent
    * @throws Exception if the database is not set.
    * @return PDO
    */
    public function db($databaseIdent = null)
    {
        // If no database ident was passed in parameter, use the class database or the config databases
        if ($databaseIdent === null) {
            $databaseIdent = $this->databaseIdent();
        }

        // If the handle was already created, reuse from static $dbh variable
        if (isset(self::$dbs[$databaseIdent])) {
            return self::$dbs[$databaseIdent];
        }

        $dbConfig = $this->databaseConfig();

        $db_hostname = (isset($dbConfig['hostname']) ? $dbConfig['hostname'] : self::DEFAULT_DB_HOSTNAME);
        $db_type = (isset($dbConfig['type']) ? $dbConfig['type'] : self::DEFAULT_DB_TYPE);
        /** @todo ... The other parameters are required. Really? */

        try {
            $database = $dbConfig['database'];
            $username = $dbConfig['username'];
            $password = $dbConfig['password'];

            // Set UTf-8 compatibility by default. Disable it if it is set as such in config
            $extra_opts = [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'];
            if (isset($dbConfig['disable_utf8']) && $dbConfig['disable_utf8']) {
                $extra_opts = null;
            }

            $db = new PDO($db_type.':host='.$db_hostname.';dbname='.$database, $username, $password, $extra_opts);

            // Set PDO options
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            if ($db_type == 'mysql') {
                $db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
            }

        } catch (PDOException $e) {
            throw new Exception(
                sprintf('Error setting up database: %s', $e->getMessage())
            );
        }

        self::$dbs[$databaseIdent] = $db;

        return self::$dbs[$databaseIdent];
    }

    /**
    * Get all the fields of a model.
    *
    * @param ModelInterface $model
    * @param array|null     $properties
    * @return array
    * @todo Move this method in StorableTrait or AbstractModel
    */
    private function getModelFields(ModelInterface $model, $properties = null)
    {
        $metadata = $model->metadata();
        if ($properties === null) {
            $properties = array_keys($model->metadata()->properties());
        } else {
            $properties = array_merge($properties, [$model->key()]);
        }

        $fields = [];
        foreach ($properties as $propertyIdent) {
            $p = $model->p($propertyIdent);
            $v = $model->propertyValue($propertyIdent);
            $p->setVal($v);
            if (!$p || !$p->active()) {
                continue;
            }
            foreach ($p->fields() as $fieldIdent => $field) {
                $fields[$field->ident()] = $field;
            }
        }
        return $fields;
    }

    /**
    * @param mixed              $ident Ident can be an integer, a string, ...
    * @param StoreableInterface $item  Optional item to load into
    * @throws Exception
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
     * @param  string                 $key   Column name
     * @param  mixed                  $ident Value of said column
     * @param  StorableInterface|null $item  Optional. Item (storable object) to load into
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
     * @param string $query The SQL query.
     * @param array $binds Optional. The query parameters.
     * @param StorableInterface $item Optional. Item (storable object) to load into.
     * @throws Exception If there is a query error.
     * @return StorableInterface Item.
     */
    public function loadItemFromQuery($query, array $binds = null, StorableInterface $item = null)
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
    * @param StorableInterface|null $item
    * @return array
    */
    public function loadItems(StorableInterface $item = null)
    {
        if ($item !== null) {
            $this->setModel($item);
        }

        $items = [];
        $model = $this->model();
        $db = $this->db();

        $q = $this->sqlLoad();
        $this->logger->debug($q);
        $sth = $db->prepare($q);
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
    * @param StorableInterface $item The object to save
    * @throws Exception if a database error occurs
    * @return mixed The created item ID, or false in case of an error
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

        $keys = [];
        $values = [];
        $binds = [];
        $binds_types = [];
        foreach ($fields as $f) {
            $k = $f->ident();
            if (in_array($k, $tableStructure)) {
                $keys[] = '`'.$k.'`';
                $values[] = ':'.$k.'';
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
            throw new Exception('Could not save item.');
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
    * @param StorableInterface $item       The object to update
    * @param array             $properties The list of properties to update, if not all
    * @return boolean Success / Failure
    */
    public function updateItem(StorableInterface $item, $properties = null)
    {
        if ($item !== null) {
            $this->setModel($item);
        }
        $model = $this->model();

        $tableStructure = array_keys($this->tableStructure());
        $fields = $this->getModelFields($model, $properties);

        $updates = [];
        $binds = [];
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
                'properties'=>$properties,
                'structure'=>$tableStructure
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
    * @throws Exception
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
    * @param string $q           The SQL query to executed
    * @param array  $binds
    * @param array  $binds_types
    * @return PDOStatement|false The PDOStatement, or false in case of error
    */
    protected function dbQuery($q, array $binds = [], array $binds_types = [])
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
    * @throws Exception if the source does not have a table defined
    * @return string
    */
    public function sqlLoad()
    {
        $table = $this->table();
        if (!$table) {
            throw new Exception('No table defined.');
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
    * @return string
    */
    protected function sqlSelect()
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
    protected function sqlFilters()
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
    }

    /**
    * @return string
    */
    protected function sqlOrders()
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
    protected function sqlPagination()
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
    * @param array $data Optional
    * @return DatabaseSourceConfig
    */
    public function createConfig(array $data = null)
    {
        $config = new DatabaseSourceConfig();
        if (is_array($data)) {
            $config->merge($data);
        }
        return $config;
    }
}
