<?php

namespace Charcoal\Source;

// Dependencies from `PHP`
use \Exception;
use \InvalidArgumentException;

// Dependencies from `PHP` modules
use \PDO;

// Intra-module (`charcoal-core`) dependencies
use \Charcoal\Charcoal;
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
    * @var string $_database_ident
    */
    private $_database_ident;
    /**
    * @var DatabaseSourceConfig $_database_config
    */
    private $_database_config;

    /**
    * @var string $_table
    */
    private $_table = null;

    /**
    * @var array $_dbs
    */
    private static $_dbs = [];

    /**
    * @param string $database_ident
    * @throws InvalidArgumentException if ident is not a string
    * @return DatabaseSource Chainable
    */
    public function set_database_ident($database_ident)
    {
        if (!is_string($database_ident)) {
            throw new InvalidArgumentException('set_database() expects a string as database ident.');
        }
        $this->_database_ident = $database_ident;
        return $this;
    }

    /**
    * Get the current database ident.
    * If null, then the project's default (from `Charcoal::config()` will be used.)
    *
    * @return string
    */
    public function database_ident()
    {
        if ($this->_database_ident === null) {
            return Charcoal::config()->default_database();
        }
        return $this->_database_ident;
    }

    /**
    * @param array $database_config
    * @throws InvalidArgumentException
    * @return DatabaseSource Chainable
    */
    public function set_database_config($database_config)
    {
        if (!is_array($database_config)) {
            throw new InvalidArgumentException('Database config needs to be an array.');
        }
        $this->_database_config = $database_config;
        return $this;
    }

    /**
    * @return mixed
    */
    public function database_config()
    {
        if ($this->_database_config === null) {
            $ident = $this->database_ident();
            return Charcoal::config()->database_config($ident);
        }
        return $this->_database_config;
    }

    /**
    * Set the database's table to use.
    *
    * @param string $table
    * @throws InvalidArgumentException if argument is not a string
    * @return DatabaseSource Chainable
    */
    public function set_table($table)
    {
        if (!is_string($table)) {
            throw new InvalidArgumentException('set_table() expects a string as table.');
        }
        $this->_table = $table;

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
        if ($this->_table === null) {
            throw new Exception('Table was not set.');
        }
        return $this->_table;
    }

    /**
    * Create a table from a model's metadata.
    *
    * @return boolean Success / Failure
    */
    public function create_table()
    {
        if ($this->table_exists()) {
            // Table already exists
            return true;
        }

        $model = $this->model();
        $metadata = $model->metadata();
        $fields = $this->_get_model_fields($model);
        $fields__sql = [];
        foreach ($fields as $field) {
            $fields_sql[] = $field->sql();
        }

        $defaults = $metadata['data'];

        $q = 'CREATE TABLE  `'.$this->table().'` ('."\n";
        $q .= implode(',', $fields_sql);
        $key = $model->key();
        if ($key) {
            $q .= ', PRIMARY KEY (`'.$key.'`) '."\n";
        }
        /** @todo add indexes for all defined list constraints (yea... tough job...) */
        $q .= ') ENGINE=MYISAM DEFAULT CHARSET=utf8 COMMENT=\''.addslashes($metadata['name']).'\';';
        // var_dump($q);
        $res = $this->db()->query($q);

        return true;
    }

    /**
    * Alter an existing table to match the model's metadata.
    *
    * @return boolean Success / Failure
    */
    public function alter_table()
    {
        if (!$this->table_exists()) {
            return false;
        }

        $fields = $this->_get_model_fields($this->model());

        $cols = $this->table_structure();

        foreach ($fields as $field) {
            $ident = $field->ident();

            if (!array_key_exists($ident, $cols)) {
                // The key does not exist at all.
                $q = 'ALTER TABLE `'.$this->table().'` ADD '.$field->sql();
                // var_dump($q);
                $res = $this->db()->query($q);
            } else {
                // The key exists. Validate.
                $col = $cols[$ident];
                $alter = false;
                if (strtolower($col['Type']) != strtolower($field->sql_type())) {
                    $alter = true;
                }
                if ((strtolower($col['Null']) == 'no') && !$field->allow_null()) {
                    $alter = true;
                }
                if ((strtolower($col['Null']) != 'no') && $field->allow_null()) {
                    $alter = true;
                }
                if ($col['Default'] != $field->default_val()) {
                    $alter = true;
                }

                if ($alter === true) {
                    $q = 'ALTER TABLE `'.$this->table().'` CHANGE `'.$ident.'` '.$field->sql();
                    // var_dump($q);
                    $res = $this->db()->query($q);
                }

            }
        }

        return true;
    }

    /**
    * @return boolean
    */
    public function table_exists()
    {
        $q = 'SHOW TABLES LIKE \''.$this->table().'\'';
        $res = $this->db()->query($q);
        $table_exists = $res->fetchColumn(0);

        // Return as boolean
        return !!$table_exists;
    }

    /**
    * Get the table columns information.
    *
    * @return array
    */
    public function table_structure()
    {
        $q = 'SHOW COLUMNS FROM `'.$this->table().'`';
        $res = $this->db()->query($q);
        $cols = $res->fetchAll((PDO::FETCH_GROUP|PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC));
        return $cols;
    }

    /**
    * Check wether the source table is empty (`true`) or not (`false`)
    *
    * @return boolean
    */
    public function table_is_empty()
    {
        $q = 'SELECT NULL FROM `'.$this->table().'` LIMIT 1';
        $res = $this->db()->query($q);
        return ($res->rowCount() === 0);
    }

    /**
    * @param string $database_ident
    * @throws Exception if the database is not set.
    * @return PDO
    */
    public function db($database_ident = null)
    {
        // If no database ident was passed in parameter, use the class database or the config databases
        if ($database_ident === null) {
            $database_ident = $this->database_ident();
        }

        // If the handle was already created, reuse from static $dbh variable
        if (isset(self::$_dbs[$database_ident])) {
            return self::$_dbs[$database_ident];
        }

        $db_config = $this->database_config();

        $db_hostname = (isset($db_config['hostname']) ? $db_config['hostname'] : self::DEFAULT_DB_HOSTNAME);
        $db_type = (isset($db_config['type']) ? $db_config['type'] : self::DEFAULT_DB_TYPE);
        /** @todo ... The other parameters are required. Really? */

        try {
            $database = $db_config['database'];
            $username = $db_config['username'];
            $password = $db_config['password'];

            // Set UTf-8 compatibility by default. Disable it if it is set as such in config
            $extra_opts = [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'];
            if (isset($db_config['disable_utf8']) && $db_config['disable_utf8']) {
                $extra_opts = null;
            }

            $db = new PDO($db_type.':host='.$db_hostname.';dbname='.$database, $username, $password, $extra_opts);

            // Set PDO options
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            if ($db_type == 'mysql') {
                $db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
            }

        } catch (PDOException $e) {
            throw new Exception('Error setting up database.');
        }

        self::$_dbs[$database_ident] = $db;

        return self::$_dbs[$database_ident];
    }

    /**
    * Get all the fields of a model.
    *
    * @param ModelInterface $model
    * @param array|null     $properties
    * @return array
    * @todo Move this method in StorableTrait or AbstractModel
    */
    private function _get_model_fields(ModelInterface $model, $properties = null)
    {
        $metadata = $model->metadata();
        if ($properties === null) {
            $properties = array_keys($model->metadata()->properties());
        } else {
            $properties = array_merge($properties, [$model->key()]);
        }

        $fields = [];
        foreach ($properties as $property_ident) {
            $p = $model->p($property_ident);
            $v = $model->property_value($property_ident);
            $p->set_val($v);
            if (!$p || !$p->active()) {
                continue;
            }
            $fields = array_merge($fields, $p->fields());
        }
        return $fields;
    }

    /**
    * @param mixed              $ident
    * @param StoreableInterface $item  Optional item to load into
    * @throws Exception
    * @return StorableInterface
    */
    public function load_item($ident, StorableInterface $item = null)
    {
        if ($item !== null) {
            $this->set_model($item);
        } else {
            $class = get_class($this->model());
            $item = new $class;
        }

        $q = '
            SELECT
                *
            FROM
               `'.$this->table().'`
            WHERE
               `'.$this->model()->key().'`=:ident
            LIMIT
               1';

        $binds = [
            'ident' => $ident
        ];
        $sth = $this->db_query($q, $binds);
        if ($sth === false) {
            throw new Exception('Error');
        }

        $data = $sth->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            $item->set_flat_data($data);
        }

        return $item;
    }

    /**
    * @param StorableInterface|null $item
    * @return array
    */
    public function load_items(StorableInterface $item = null)
    {
        if ($item !== null) {
            $this->set_model($item);
        }

        $items = [];
        $model = $this->model();
        $db = $this->db();

        $q = $this->sql_load();
        Charcoal::logger()->debug($q);
        $sth = $db->prepare($q);
        $sth->execute();
        $sth->setFetchMode(PDO::FETCH_ASSOC);

        $classname = get_class($model);
        while ($obj_data = $sth->fetch()) {
            $obj = new $classname;
            $obj->set_flat_data($obj_data);
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
    public function save_item(StorableInterface $item)
    {
        if ($this->table_exists() === false) {
            /** @todo Optionnally turn off for some models */
            $this->create_table();
        }

        if ($item !== null) {
            $this->set_model($item);
        }
        $model = $this->model();

        $table_structure = array_keys($this->table_structure());

        $fields = $this->_get_model_fields($model);

        $keys = [];
        $values = [];
        $binds = [];
        $binds_types = [];
        foreach ($fields as $f) {
            $k = $f->ident();
            if (in_array($k, $table_structure)) {
                $keys[] = '`'.$k.'`';
                $values[] = ':'.$k.'';
                $binds[$k] = $f->val();
                $binds_types[$k] = $f->sql_pdo_type();
            }
        }

        $q = '
            INSERT
                INTO
            `'.$this->table().'`
                ('.implode(', ', $keys).')
            VALUES
                ('.implode(', ', $values).')';

        $res = $this->db_query($q, $binds, $binds_types);

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
    public function update_item(StorableInterface $item, $properties = null)
    {
        if ($item !== null) {
            $this->set_model($item);
        }
        $model = $this->model();

        $table_structure = array_keys($this->table_structure());
        $fields = $this->_get_model_fields($model, $properties);

        $updates = [];
        $binds = [];
        $binds_types = [];
        foreach ($fields as $f) {
            $k = $f->ident();
            if (in_array($k, $table_structure)) {
                if ($k !== $model->key()) {
                    $updates[] = '`'.$k.'` = :'.$k;
                }
                $binds[$k] = $f->val();
                $binds_types[$k] = $f->sql_pdo_type();
            } else {
                Charcoal::logger()->debug(
                    sprintf('Field %s not in table structure', $k)
                );
            }
        }

        $q = '
            UPDATE
                `'.$this->table().'`
            SET
                '.implode(", \n\t", $updates).'
            WHERE
                `'.$model->key().'`=:'.$model->key().'
            LIMIT
                1';

        $res = $this->db_query($q, $binds, $binds_types);

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
    public function delete_item(StorableInterface $item = null)
    {
        if ($item !== null) {
            $this->set_model($item);
        }
        $model = $this->model();

        if (!$model->id()) {
            throw new Exception('Can not delete item. No ID.');
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

        $res = $this->db_query($q, $binds);

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
    protected function db_query($q, array $binds = [], array $binds_types = [])
    {
        Charcoal::logger()->debug($q, $binds);
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
    public function sql_load()
    {
        $table = $this->table();
        if (!$table) {
            throw new Exception('No table defined.');
        }

        $selects = $this->sql_select();
        $tables  = '`'.$table.'` AS obj_table';
        $filters = $this->sql_filters();
        $orders  = $this->sql_orders();
        $limits  = $this->sql_pagination();

        $q = 'SELECT '.$selects.' FROM '.$tables.$filters.$orders.$limits;
        return $q;
    }

    /**
    * @return string
    */
    protected function sql_select()
    {
        $properties = $this->properties();
        if (empty($properties)) {
            return 'obj_table.*';
        }

        $sql = '';
        $props_sql = [];
        foreach ($properties as $p) {
            $props_sql[] = 'obj_table.`'.$p.'`';
        }
        if (!empty($props_sql)) {
            $sql = implode(', ', $props_sql);
        }

        return $sql;
    }

    /**
    * @return string
    * @todo 2015-03-04 Use bindings for filters value
    */
    protected function sql_filters()
    {
        $sql = '';
        $filters = $this->filters();
        // Process filters
        if (!empty($filters)) {
            $filters_sql = [];
            foreach ($filters as $f) {
                $f_sql = $f->sql();
                if ($f_sql) {
                    $filters_sql[] = [
                        'sql'     => $f->sql(),
                        'operand' => $f->operand()
                    ];
                }
            }
            if (!empty($filters_sql)) {
                $sql .= ' WHERE';
                $i = 0;

                foreach ($filters_sql as $f) {
                    if ($i > 0) {
                        $sql .= ' '.$f['operand'];
                    }
                    $sql .= ' '.$f['sql'];
                    $i++;
                }
            }

        }
        return $sql;
    }

    /**
    * @return string
    */
    protected function sql_orders()
    {
        $sql = '';

        if (!empty($this->_orders)) {
            $orders_sql = [];
            foreach ($this->_orders as $o) {
                $orders_sql[] = $o->sql();
            }
            if (!empty($orders_sql)) {
                $sql = ' ORDER BY '.implode(', ', $orders_sql);
            }
        }

        return $sql;
    }

    /**
    * @return string
    */
    protected function sql_pagination()
    {
        return $this->pagination()->sql();
    }

    /**
    * @return FilterInterface
    */
    protected function create_filter()
    {
        $filter = new DatabaseFilter();
        return $filter;
    }

    /**
    * @return OrderInterface
    */
    protected function create_order()
    {
        $order = new DatabaseOrder();
        return $order;
    }

    /**
    * @return PaginationInterface
    */
    protected function create_pagination()
    {
        $pagination = new DatabasePagination();
        return $pagination;
    }

    /**
    * ConfigurableTrait > create_config()
    *
    * Overrides the method defined in AbstractSource to returns a `DatabaseSourceConfig` object.
    *
    * @param array $data Optional
    * @return DatabaseSourceConfig
    */
    public function create_config(array $data = null)
    {
        $config = new DatabaseSourceConfig();
        if (is_array($data)) {
            $config->set_data($data);
        }
        return $config;
    }
}
