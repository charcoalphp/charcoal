<?php

namespace Charcoal\Model;

use \Charcoal\Charcoal as Charcoal;
use \PDO as PDO;
use \PDOException as PDOException;

/**
*
*/
class Source
{
	const DEFAULT_DB_HOSTNAME = 'localhost';
	const DEFAULT_DB_TYPE = 'mysql';

	private $_model;
	private $_table = null;

	/**
	* @throws \InvalidArgumentException if argument is not a string
	*/
	public function set_table($table)
	{
		if(!is_string($table)) {
			throw new \InvalidArgumentException('set_table() expects a string as table');
		}
		$this->_table = $table;

		return $this;
	}

	/**
	* @throws \Exception if the table was not set
	*/
	public function table()
	{
		if($this->_table === null) {
			throw new \Exception('Table was not set.');
		}
		return $this->_table;
	}

	/**
	* @var Model $models
	* @return Source Chainable
	*/
	public function set_model(Object $model)
	{
		$this->_model = $model;
		return $this;
	}

	public function model()
	{
		if($this->_model === null) {
			throw new \Exception('No model set.');
		}
		return $this->_model;
	}

	public function create_table()
	{
		if($this->table_exists()) {
			// Table already exists
			return true;
		}

		$model = $this->model();
		$metadata = $model->metadata();
		$fields = $this->_get_model_fields($model);

		$defaults = $metadata['data'];

		$q = 'CREATE TABLE  `'.$this->table().'` ('."\n";
		foreach($fields as $field) {
			$default = $field->default_val() ? ' DEFAULT \''.addslashes($field->default_val()).'\' ' : '';
			$null = !$field->allow_null() ? ' NOT NULL ' : '';
			$comment = $field->label() ? ' COMMENT \''.addslashes($field->label()).'\' ' : '';
			$q .= '`'.$field->ident().'` '.$field->sql_type().$null.$default.$comment.', '."\n";
		}
		$key = $model->key();
		if($key) {
			$q .= ' PRIMARY KEY (`'.$model->key().'`) '."\n";
		}
		// @todo add indexes for all defined list constraints (yea... tough job...)
		$q .= ') ENGINE = MYISAM DEFAULT CHARSET=utf8 COMMENT=\''.addslashes($metadata['name']).'\';';
	
		$res = $this->db()->query($q);

		return true;
	}

	public function alter_table()
	{
		if(!$this->table_exists()){
			return false;
		}

		$fields = $this->_get_model_fields($this->model());

		$q = 'SHOW COLUMNS FROM `'.$this->table().'`';
		$res = $this->db()->query($q);
		$cols = $res->fetchAll((PDO::FETCH_GROUP|PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC));

		foreach($fields as $field) {
			$ident = $field->ident();

			$default = $field->default_val() ? ' DEFAULT \''.addslashes($field->default_val()).'\' ' : '';
			$null = ($field->allow_null() === false) ? ' NOT NULL ' : '';
			$comment = $field->label() ? ' COMMENT \''.addslashes($field->label()).'\' ' : '';
			$sql_type = $field->sql_type();

			if(!array_key_exists($ident, $cols)) {
				// The key does not exist at all.
				$q = 'ALTER TABLE `'.$this->table().'` ADD `'.$ident.'` '.$sql_type.$null.$default.$comment;
				$res = $this->db()->query($q);
			}
			else {
				// The key exists. Validate.
				$col = $cols[$ident];
				$alter = false;
				if(strtolower($col['Type']) != strtolower($sql_type)) {
					$alter = true;
				}
				if((strtolower($col['Null']) == 'no') && !$field->allow_null()) {
					$alter = true;
				}
				if((strtolower($col['Null']) != 'no') && $field->allow_null()) {
					$alter = true;
				}
				if($col['Default'] != $field->default_val()) {
					$alter = true;
				}

				if($alter) {
					$q = 'ALTER TABLE `'.$this->table().'` CHANGE `'.$ident.'` `'.$ident.'` '.$sql_type.$null.$default.$comment;
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
	* @throws \Exception if the database is not set.
	*/
	public function db($database=null)
	{
		static $dbh;
		if(!is_array($dbh)) {
			$dbh = [];
		}

		$cfg = Charcoal::$config;
		
		// If no param was 
		if($database === null) {
			$database = isset($cfg['default_database']) ? $cfg['default_database'] : '';
		}
		if(!$database) {
			throw new \Exception('No default database found in config');
		}
		
		// Make sure this database was configured.
		if(!isset($cfg['databases']) || !isset($cfg['databases'][$database])) {
			throw new \Exception('Invalid database');
		}
		
		// If the handle was already created, reuse from static $dbh variable
		if(isset($dbh[$database])) {
			return $dbh[$database];
		}
		
		// The handle was never created: create it.

		$db_config = $cfg['databases'][$database];

		$db_hostname = isset($db_config['hostname']) ? $db_config['hostname'] : self::DEFAULT_DB_HOSTNAME; // Default to localhost
		$db_type = isset($db_config['type']) ? $db_config['type'] : self::DEFAULT_DB_TYPE; // Nothing else is really supported for now anyway
		// ... The other parameters are required. @todo Really?
		
		try {
			
			// Set UTf-8 compatibility by default. Disable it if it is set as such in config
			$extra_opts = [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'];
			if(isset($db_config['disable_utf8']) && $db_config['disable_utf8']) {
				$extra_opts = null;
			}
			// PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION

			$dbh[$database] = new PDO($db_type.':host='.$db_hostname.';dbname='.$db_config['database'], $db_config['username'], $db_config['password'], $extra_opts);
			
			// Set PDO options
			$dbh[$database]->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			if($db_type == 'mysql') {
				$dbh[$database]->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
			}
			
		} 
		catch (PDOException $e) {
			throw new \Exception('Error setting up database');
		}
		
		return $dbh[$database];
	}

	private function _get_model_fields(Model $model)
	{
		$metadata = $model->metadata();
		$properties = $metadata->properties();

		$fields = [];
		foreach($properties as $property_ident => $property_options) {
			$p = $model->p($property_ident);
			if(!$p || !$p->active()) {
				continue;
			}
			$fields = array_merge($fields, $p->fields());
		}
		return $fields;
	}
}