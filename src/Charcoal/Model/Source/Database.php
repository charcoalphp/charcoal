<?php

namespace Charcoal\Model\Source;

use \PDO as PDO;
use \Charcoal\Charcoal as Charcoal;
use \Charcoal\Model\Source as Source;
use \Charcoal\Model\Model as Model;

class Database extends Source
{
	const DEFAULT_DB_HOSTNAME = 'localhost';
	const DEFAULT_DB_TYPE = 'mysql';

	private $_database_ident = null;
	private $_database_config = null;

	private $_table = null;

	private static $_dbs = [];

	public function set_database_ident($database_ident)
	{
		if(!is_string($table)) {
			throw new \InvalidArgumentException('set_database() expects a string as database ident');
		}
		$this->_database_ident = $database_ident;
		return $this;
	}

	public function database_ident()
	{
		if($this->_database_ident === null) {
			return Charcoal::config()->default_database();
		}
		return $this->_database_ident;
	}

	public function set_database_config($database_config)
	{
		if(!is_array($database_config)) {
			throw new \Exception('Database config needs to be an array.');
		}
		$this->_database_config = $database_config;
		return $this;
	}

	public function database_config()
	{
		if($this->_database_config === null) {
			$ident = $this->database_ident();
			return Charcoal::config()->database_config($ident);
		}
		return $this->_database_config;
	}

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


	public function create_table()
	{
		if($this->table_exists()) {
			// Table already exists
			return true;
		}

		$model = $this->model();
		$metadata = $model->metadata();
		$fields = $this->_get_model_fields($model);
		$fields__sql = [];
		foreach($fields as $field) {
			$fields_sql[] = $field->sql();
		}

		$defaults = $metadata['data'];

		$q = 'CREATE TABLE  `'.$this->table().'` ('."\n";
		$q .= implode(',', $fields_sql);
		$key = $model->key();
		if($key) {
			$q .= ', PRIMARY KEY (`'.$key.'`) '."\n";
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

			if(!array_key_exists($ident, $cols)) {
				// The key does not exist at all.
				$q = 'ALTER TABLE `'.$this->table().'` ADD '.$field->sql();
				$res = $this->db()->query($q);
			}
			else {
				// The key exists. Validate.
				$col = $cols[$ident];
				$alter = false;
				if(strtolower($col['Type']) != strtolower($field->sql_type())) {
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

				if($alter === true) {
					$q = 'ALTER TABLE `'.$this->table().'` CHANGE `'.$ident.'` '.$field->sql();
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
	public function db($database_ident=null)
	{
		// If no database ident was passed in parameter, use the class database or the config databases
		if($database_ident === null) {
			$database_ident = $this->database_ident();
		}

		// If the handle was already created, reuse from static $dbh variable
		if(isset(self::$_dbs[$database_ident])) {
			return self::$_dbs[$database_ident];
		}

		$db_config = $this->database_config();

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

			$db = new PDO($db_type.':host='.$db_hostname.';dbname='.$db_config['database'], $db_config['username'], $db_config['password'], $extra_opts);
			
			// Set PDO options
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			if($db_type == 'mysql') {
				$db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
			}
			
		} 
		catch (PDOException $e) {
			throw new \Exception('Error setting up database');
		}
		
		self::$_dbs[$database_ident] = $db;

		return self::$_dbs[$database_ident];
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