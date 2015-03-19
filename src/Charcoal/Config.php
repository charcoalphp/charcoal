<?php

namespace Charcoal;

class Config implements \ArrayAccess
{
	const DEFAULT_APPLICATION_ENV = 'live';

	public $ROOT;
	public $URL;

	private $_project_name;
	private $_dev_mode;

	private $_timezone;

	public $_salt;

	public $cache;

	private $_databases;
	private $_default_database;

	public $metadata_path;
	public $templates_path;

	public function __construct($config=null)
	{
		$this->add_file(__DIR__.'/../../config/config.default.json');

		if($config !== null) {
			if(is_string($config)) {
				$this->add_file($config);
			}
			else if(is_array($config)) {
				$this->set_data($config);
			}
		}

	}

	public function offsetExists($offset)
	{
		return isset($this->{$offset});
	}

	public function offsetGet($offset)
	{
		return isset($this->{$offset}) ? $this->{$offset} : null;
	}

	public function offsetSet($offset, $value)
	{
		$this->{$offset} = $value;
	}

	public function offsetUnset($offset)
	{
		$this->{$offset} = null;
		unset($this->{$offset});
	}

	public function set_data($data)
	{
		if(isset($data['dev_mode'])) {
			$this->set_dev_mode($data['dev_mode']);
			unset($data['dev_mode']);
		}
		if(isset($data['timezone'])) {
			$this->set_timezone($data['timezone']);
			unset($data['timezone']);
		}

		if(isset($data['databases'])) {
			$this->set_databases($data['databases']);
			unset($data['databases']);
		}
		if(isset($data['default_database'])) {
			$this->set_default_database($data['default_database']);
			unset($data['default_database']);
		}


		foreach($data as $k => $v) {
			$this->{$k} = $v;
		}

		return $this;
	}

	/**
	* @param string $filename
	* @throws \InvalidArgumentException if the filename is not a string or 
	* @return Config (Chainable)
	* @todo Load with Flysystem
	*/
	public function add_file($filename)
	{
		if(!is_string($filename)) {
			throw new \InvalidArgumentException('');
		}

		if(pathinfo($filename, PATHINFO_EXTENSION) == 'php') {
			include $filename;
		}
		else if(pathinfo($filename, PATHINFO_EXTENSION) == 'json') {
			if(file_exists($filename)) {
				$file_content = file_get_contents($filename);
				$config = json_decode($file_content, true);
				$this->set_data($config);
			}
		}
		else {
			throw new \InvalidArgumentException('Only json and php files are accepted as config file.');
		}

		return $this;
	}

	public function project_name()
	{
		return $this->project_name;
	}

	public function salt()
	{
		return $this->_salt;
	}

	public function application_env()
	{
		$application_env = preg_replace('/!^[A-Za-z0-9_]+$/', '', getenv('APPLICATION_ENV'));
		if(!$application_env) {
			$application_env = self::DEFAULT_APPLICATION_ENV;
		}
		return $application_env;
	}

	public function set_dev_mode($dev_mode)
	{
		if(!is_bool($dev_mode)) {
			throw new \InvalidArgumentException('Dev mode must be a boolean.');
		}
		$this->_dev_mode = $dev_mode;
		return $this;
	}

	public function dev_mode()
	{
		return !!$this->_dev_mode;
	}

	public function set_timezone($timezone)
	{
		if(!is_string($timezone)) {
			throw new \InvalidArgumentException('Timezone must be a string.');
		}
		$this->_timezone = $timezone;
		return $this;
	}

	public function timezone()
	{
		return $this->_timezone;
	}

	public function set_databases($databases)
	{
		if(!is_string($default_database)) {
			throw new \InvalidArgumentException('Default database must be a string.');
		}
		$this->_databases = $databases;
		return $this;
	}
	
	public function databases()
	{
		if($this->_databases == null) {
			throw new \Exception('Databases are not set');
		}
		return $this->_databases;
	}

	public function database_config($ident)
	{
		if(!is_string($ident)) {
			throw new \InvalidArgumentException('Default database must be a string.');
		}
		$databases = $this->databases();
		if(!isset($databases[$ident])) {
			throw new \Exception(sprintf('No database configuration matches "%s"', $ident));
		}
		return $databases[$ident];
	}

	public function set_default_database($default_database)
	{
		if(!is_string($default_database)) {
			throw new \InvalidArgumentException('Default database must be a string.');
		}
		$this->_default_database = $default_database;
	}

	public function add_database($ident, $config)
	{
		if(!is_string($ident)) {
			throw new \InvalidArgumentException('Database ident must be a string.');
		}
		if(!is_array($config)) {
			throw new \InvalidArgumentException('Database config must be an array.');
		}

		if($this->_databases === null) {
			$this->_databases = [];
		}
		$this->_databases[$ident] = $config;
		return $this;
	}

	public function default_database()
	{
		if($this->_default_database == null) {
			throw new \Exception('Default database is not set.');
		}
		return $this->_default_database;
	}
}
