<?php

namespace Charcoal\Model;

use \Charcoal\Charcoal as Charcoal;
use \PDO as PDO;
use \PDOException as PDOException;

class Source
{
	const DEFAULT_DB_HOSTNAME = 'localhost';
	const DEFAULT_DB_TYPE = 'mysql';

	private $_table;


	public function set_table($table)
	{
		if(!is_string($table)) {
			throw new \InvalidArgumentException('set_table() expects a string as table');
		}
		$this->_table = $table;

		return $this;
	}

	public function table()
	{
		return $this->_table;
	}

	public function db($database=null)
	{
		static $dbh;
		if(!is_array($dbh)) {
			$dbh = [];
		}

		$cfg = Charcoal::$config;
		
		// If no param was 
		if($database===null) {
			$database = isset($cfg['default_database']) ? $cfg['default_database'] : '';
		}
		if(!$database) {
			throw new \Exception('No database found');
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
}