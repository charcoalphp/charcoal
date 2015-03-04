<?php
/**
* Charcoal Cache File
*
* @category Charcoal
* @package Core
* @subpackage Utilities
*
* @author Mathieu Ducharme <mat@locomotive.ca>
* @copyright 2014 Locomotive
* @license LGPL <https://www.gnu.org/licenses/lgpl.html>
* @version 2015-02-24
* @link http://charcoal.locomotive.ca
* @since Version 2014-03-20
*/

// Namespace: todo
namespace Charcoal\Helper;

use \Charcoal\Charcoal as Charcoal;

/**
* Charcoal Cache Utilities
*
* The cache system can use multiple backend.
* As of 2015-02-24, only the `apc` and `memcached` backends are supported.
*
* The cache system
*
* ***
* # Cache Configuration
*
* The cache configuration is controlled through the main global Charcoal configuration, in the
* "cache" key. An example structure, with the actual configuration, can be seen below:
* ``` json
* {
* 	"cache":{
* 
* 		"active":true
* 		
* 		"type":"apc",
* 		
* 		"default_ttl":0,
* 		"prefix":"",
* 
* 		"options":{
* 			"apc":{
* 				
* 			},
* 			"memcache":{
*				"servers":[
*					{
* 						"host":"localhost",
* 						"port":11211
*					}
*				]
* 			},
* 			"void":{}
* 		}
* 	}
* 
* }
* ```
*
* A detailed explanation of all configuration keys is in the table below:
* ```
* Property        | Type    | Default value | Description                        
* ==========      | ======= | ============= | =========== 
* **active**      | boolean | true          | If false, then the cache system is disabled. 
* **type**        | string  | apc           | Can be "apc", "memcache", "file", "db" or "void". 
* **default_ttl** | integer | 0             | The default "time-to-live" for cache entries, in seconds. 
* **options**     | json    | N/A           | Extra type-specific type options.
* ```
*
* Each cache type can have extra configuration options, defined in the configuration options[$type] key.
*
* ## APC Options
* None are used for now. APC configuration is read from the standard php.ini settings.
*
* ## Memcached Options
* ```
* Sub-Property   | Type    | Default value | Description
* -------------- | ------- | ------------- | -----------
* **server**     | string  | localhost     | The memcache server hostname or ip address.
* **port**       | integer | 11211         | The memcache server port.
* **persistent** | boolean | false         | Use persistent connection
* ```
* *Note* that as of 2015-01-19, memcache support is still *experimental* in Charcoal.
* *Note* that multiple servers (`addServer()`) is not yet supported.
*
* ***
* # How to use
*
* ## Storing a value
* ``` php
* \Charcoal\Helper\Cache::get()->store($key, $value, $ttl);
* ```
* {@link \Charcoal\Helper\Cache::store()}
*
* ## Fetching a stored value
* ``` php
* \Charcoal\Helper\Cache::get()->fetch($key);
* ```
* {@link \Charcoal\Helper\Cache::fetch()}
*
* ## Checking if a key exists
* ``` php
* \Charcoal\Helper\Cache::get()->exists($key);
* ```
* {@link Charcoa_Cache::exists()}
*
* ## Advanced usage
* ``` php
* \Charcoal\Helper\Cache::get()->set_type('apc')
*	->set_default_ttl(500)
*	->set_prefix('my_custom_prefix')
*	->store($key, $value);
* ```
*
* ## Clearing the cache
* It is possible to delete a specific key stored in the cache or to completely clear the cache.
* To delete a specific key:
* ``` php
* \Charcoal\Helper\Cache::get()->delete($key);
* ```
* And to clear the entire cache. Note that this *completely* clear the cache, for all systems on the server
* ``` php
* \Charcoal\Helper\Cache::get()->clear();
* ```
*
* @category Charcoal
* @package Core
* @subpackage Utilities
*
* @author Mathieu Ducharme <mat@locomotive.ca>
* @copyright 2014 Locomotive
* @license LGPL <https://www.gnu.org/licenses/lgpl.html>
* @version 2014-03-20
* @link http://charcoal.locomotive.ca
* @since Version 2014-03-20
*/
class Cache
{
	const TYPE_APC = 'apc';
	const TYPE_MEMCACHE = 'memcache';
	const TYPE_VOID = 'void';

	const DEFAULT_TYPE = self::TYPE_MEMCACHE;

	/**
	* Can be `apc`, `memcache`, `file`, `db` or `void`.
	*
	* @var string $type
	*/
	private $type;

	/**
	* The key prefix for the cache, to ensure uniqueness on server.
	*
	* @var string $type
	*/
	private $prefix;


	/**
	* Default TTL (Time-to-live) value.
	* @var integer $default_ttl
	*/
	private $default_ttl;

	/**
	* If it is false, then cache is ignored.
	* @var boolean $active
	*/
	private $active;

	/**
	* In the case of a Memcache-based cache, keep a copy of the Memcache class
	* @var Memcache $_memcache
	*/
	private $_memcache;

	/**
	* Get a Cache instance
	*
	* @param string $type The cache type to get (apc, memcache, file, db or void)
	* @param array $fallback_types A list of types to fallback on, if the one passed through $type is not available
	*
	* @return \Charcoal\Helper\Cache
	*/
	static public function get($type=self::DEFAULT_TYPE, $fallback_types=[self::TYPE_VOID])
	{
		static $cache;
		if(!$cache) {
			$cache = new Cache($type, $fallback_types);
		}
		return $cache;
	}

	/**
	* Get wether the cache is active or not.
	*
	* @return boolean
	*/
	public function active()
	{
		$default_active = true;
		if(!isset(Charcoal::$config['cache'])) {
			return $default_active;
		}
		if(!isset(Charcoal::$config['cache']['active'])) {
			return $default_active;
		}

		return !!Charcoal::$config['cache']['active'];
	}

	/**
	* Constructor for \Charcoal\Helper\Cache
	*
	* @param string $type The cache type to get (apc, memcache, file, db or void)
	* @param array $fallback_types A list of types to fallback on, if the one passed through $type is not available
	*/
	public function __construct($type=self::DEFAULT_TYPE, $fallback_types=[self::TYPE_VOID])
	{
		$type_available = $this->is_type_available($type);
		// @todo Load $type_options instead of that blank array
		$type_valid = $this->is_type_valid($type);
		if($type_available && $type_valid) {
			$this->set_type($type);
		}
		else {
			foreach($fallback_types as $t) {
				$t_available = $this->is_type_available($t);
				$t_valid = $this->is_type_valid($t);
				if($type_available && $t_valid) {
					$this->set_type($t);
					break;
				}
			}
			
		}
		if(!$this->type) {
			// Void is always valid
			$this->set_type(self::TYPE_VOID);
		}

		// Init cache, if necessary
		$this->init();

		// Set the prefix
		$this->set_prefix();
	}

	/**
	* Initialize the cache
	*/
	public function init()
	{
		$type = $this->type();
		$opts = $this->type_options();

		switch($type) {
			case self::TYPE_APC:
				// Nothing to do for APC
				return;
			//break;

			case self::TYPE_MEMCACHE:
				$default_opts = [
					'servers' => [
						'host'	 		=> 'localhost',
						'port'			=> 11211,
						'persistent' 	=> false,
						'weight'		=> 1
					]
				];
				$opts = Charcoal::merge($default_opts, $opts);
				$this->_memcache = new \Memcache();
				if(count($opts['servers']) == 1) {
					
					$s = $opts['servers'][0];

					$host = isset($s['host']) ? $s['host'] : 'localhost';
					$port = isset($s['port']) ? $s['port'] : 11211;
					$persistent = isset($s['persistent']) ? !!$s['persistent'] : false;

					if($persistent) {
						$connect = $this->_memcache->pconnect($host, $port);
					}
					else {
						$connect = $this->_memcache->connect($host, $port);
					}
					if(!$connect) {
						// @todo Debug error.
						// Connection failed, invalidate the memcache object.
						$this->_memcache = null;
					}
				}
				else {
					foreach($opts['servers'] as $s) {
						$host = isset($s['host']) ? $s['host'] : 'localhost';
						$port = isset($s['port']) ? $s['port'] : 11211;
						$persistent = isset($s['persistent']) ? !!$s['persistent'] : false;
						$weight = isset($s['weight']) ? (int)$s['weight'] : 1;

						$this->_memcache->addServer($host, $port, $persistent, $weight);
					}
				}
				
			break;

			case self::TYPE_VOID:
				/// Nothing to do, which is the whole point
				return;
			//break;
		}
	}

	/**
	*
	*
	* @param string $type
	*
	* @return boolean
	*/
	public function is_type_available($type)
	{
		switch($type) {
			case self::TYPE_APC:
				return !!extension_loaded('apc');
			//break;

			case self::TYPE_MEMCACHE:
				return !!class_exists('Memcache');
			//break;

			case self::TYPE_VOID:
				// Always supported
				return true;
			//break;
		}
	}

	/**
	* Test if the type (passed in parameter) is valid or not.
	*
	* @param string $type The cache type to test
	*
	* @return boolean
	*/
	private function is_type_valid($type) 
	{
		$type_options = $this->type_options($type);
		return true;
	}

	/**
	* Get the options a specific type.
	*
	*
	*
	* @param string $type The cache type to test
	*
	* @return array
	*/
	private function type_options($type=null)
	{
		if($type === null) {
			$type = $this->type();
		}
		if(!isset(Charcoal::$config['cache']) || !isset(Charcoal::$config['cache'][$type])) {
			// Always return array
			return [];
		}
		else {
			return Charcoal::$config['cache'][$type];
		}
	}

	/**
	* Type getter
	* 
	* @return string
	*/
	public function type()
	{
		return $this->type;
	}

	/**
	* Type setter
	*
	* @param string $type The cache type (apc, memcache, file, db or void)
	*
	* @return \Charcoal\Helper\Cache (Chainable)
	*/
	public function set_type($type)
	{
		$this->type = $type;

		// Chainable
		return $this;
	}

	/**
	* Get the prefix
	* 
	* @return string
	*/
	public function prefix()
	{
		if(!$this->prefix) {
			// Set the prefix if it was not
			$this->set_prefix();
		}

		return $this->prefix;
	}

	/**
	* Set the key prefix. 
	*
	* If no argument is specified, then generate the prefix from the configuration file (the preferred method)
	*
	* Obviously, changing the prefix will "invalidate" any items previously in cache because the keys
	* will not exist anymore. Use with caution.
	*
	* @param string $prefix The prefix value
	*
	* @return \Charcoal\Helper\Cache
	*
	* @see \Charcoal\Helper\Cache::prefix_from_config()
	*/
	public function set_prefix($prefix=null)
	{
		if($prefix === null) {
			$prefix = $this->prefix_from_config();
		}
		$this->prefix = $prefix;

		// Chainable
		return $this;
	}

	/**
	* Get the prefix from the configuration.
	*
	*
	* @return string
	*/
	public function prefix_from_config()
	{
		$cfg = Charcoal::$config;
		$project_name = isset($cfg['project_name']) ? $cfg['project_name'] : '';
		$project_db = isset($cfg['default_database']) ? $cfg['default_database'] : '';
		$cache_prefix = isset($cfg['cache']['prefix']) ? $cfg['cache']['prefix'] : 'charcoal_';
		$prefix = $cache_prefix.$project_name.'_'.$project_db.'_';

		return $prefix;
	}

	/**
	* Helper function to return the proper TTL
	*
	* @param integer $ttl 
	*
	* @return integer
	*/
	public function ttl($ttl=null)
	{
		if($ttl === null) {
			// Use default time-to-live in case of null
			return $this->default_ttl();
		}
		if(!is_int($ttl)) {
			// Use default ttl if it was impossible
			return $this->default_ttl();
		}
		return $ttl;
	}

	/**
	* Return the default TTL (time-to-live), in seconds
	*
	* The default ttl is usually set with the global Charcoal configuration.
	* If none is set, then 0 (no limit) is always used.
	*
	* @return integer
	*/
	public function default_ttl()
	{
		if($this->default_ttl) {
			return $this->default_ttl;
		}

		// No default, then 0 (unlimited)
		return 0;
	}

	/**
	* Set the default TTL
	*
	* @param integer $ttl
	*
	* @return \Charcoal\Helper\Cache (Chainable)
	*/
	public function set_default_ttl($ttl=null)
	{
		if(($ttl === null) || !is_int($ttl)) {
			// Get the default TTL from config
			$ttl = 0; // @TODO
		}
		if(!is_int($ttl)) {
			$ttl = 0;
		}
		$this->default_ttl = $ttl;

		// Chainable
		return $this;
	}

	/**
	* Add a variable to the cache
	*
	* @param string 	$key	The key ident for cache
	* @param mixed 		$data	The value to store into cache
	* @param integer	$ttl 	Time to live, in seconds
	*
	* @return boolean True on success, false on failure
	*
	* @version 2012-04-10
	* @since Version 2012-04-10
	*
	* @todo 2012-05-13 Check if apc is properly setup first
	* @todo 2012-05-13 Insert project name in the cache key name, to allow multi-project cache on same server
	*
	* @link http://php.net/manual/en/function.apc-store.php
	*/
	public function store($key, $data, $ttl=0)
	{
		if(!$this->active()) {
			return false;
		}
		$prefix = $this->prefix();
		$ttl = $this->ttl($ttl);
		$type = $this->type();

		switch($type) {
			case self::TYPE_APC:
				return apc_store($prefix.$key, $data, $ttl);
			//break;

			case self::TYPE_MEMCACHE:
				if($this->_memcache === null) {
					return false;
				}
				$flag = 0; // MEMCACHE_COMPRESSED
				return $this->_memcache->set($prefix.$key, $data, $flag, $ttl);
			//break;

			case self::TYPE_VOID:
				// Void never works
				return false;
			//break;
		}
		
	}

	/**
	* Test if a key exists in cache
	*
	* @param string $key The key ident for cache
	*
	* @return boolean True if the key exists, false if not
	*
	* @link http://www.php.net/manual/en/function.apc-fetch.php
	*/
	public function exists($key)
	{
		if(!$this->active()) {
			return false;
		}
		$prefix = $this->prefix();
		$type = $this->type();
		
		switch($type) {
			case self::TYPE_APC:
				$exists = false;
				apc_fetch($prefix.$key, $exists);
				return $exists;
			//break;

			case self::TYPE_MEMCACHE:
				if($this->_memcache === null) {
					return false;
				}
				return !!$this->_memcache->get($prefix.$key);
			//break;

			case self::TYPE_VOID:
				// Void never works
				return false;
			//break;
		}

	}

	/**
	* Fetch the data stored in cache
	*
	* @param string $key
	*
	* @return mixed
	*
	* @version 2012-04-10
	* @since Version 2012-04-10
	*
	* @todo 2012-05-13 Check if apc is properly setup first
	* @todo 2012-05-13 Insert project name in the cache key name, to allow multi-project cache on same server
	*
	* @link http://www.php.net/manual/en/function.apc-fetch.php
	*/
	public function fetch($key)
	{
		if(!$this->active()) {
			return false;
		}
		$prefix = $this->prefix();
		$type = $this->type();
		
		switch($type) {
			case self::TYPE_APC:
				return apc_fetch($prefix.$key);
			//break;

			case self::TYPE_MEMCACHE:
				if($this->_memcache === null) {
					return false;
				}
				return $this->_memcache->get($prefix.$key);
			//break;

			case self::TYPE_VOID:
				// Void never works
				return false;
			//break;
		}

	}

	/**
	* Delete the cache data at a certain key
	*
	* @param string $key
	*
	* @return boolean True on success, false on failure
	*
	* @version 2012-04-10
	* @since Version 2012-04-10
	*
	* @todo 2012-05-13 Check if apc is properly setup first
	* @todo 2012-05-13 Insert project name in the cache key name, to allow multi-project cache on same server
	*
	* @link http://www.php.net/manual/en/function.apc-delete.php
	*/
	public function delete($key)
	{
		if(!$this->active()) {
			return false;
		}
		$prefix = $this->prefix();
		$type = $this->type();
		
		switch($type) {
			case self::TYPE_APC:
				return apc_delete($prefix.$key);
			//break;

			case self::TYPE_MEMCACHE:
				if($this->_memcache === null) {
					return false;
				}
				return $this->_memcache->delete($prefix.$key);
			//break;

			case self::TYPE_VOID:
				// Void never works
				return false;
			//break;
		}
	}

	/**
	* Completely clears the cache
	*
	* This means clearing the APC "user" cache
	* **Warning:** This clears the cache of ALL the projects on the server
	*
	* @return boolean True on success, false on failure
	*
	* @link http://www.php.net/manual/en/function.apc-clear-cache.php
	*/
	public function clear()
	{
		if(!$this->active()) {
			return false;
		}
		$prefix = $this->prefix();
		$type = $this->type();
		
		switch($type) {
			case self::TYPE_APC:
				//return apc_clear_cache('user');
				$keys = new \APCIterator('user', '/^'.$prefix.'/', APC_ITER_VALUE);
				return apc_delete($keys);				
			//break;

			case self::TYPE_MEMCACHE:
				// @todo
				if($this->_memcache === null) {
					return false;
				}
				$this->_memcache->flush();
				// Memcache invalidates any cache in th same second as the flush. So sleep for 1 full second to ensure nothing will be lost.
				sleep(1);
				return true;
			//break;

			case self::TYPE_VOID:
				// Void never works
				return false;
			//break;
		}
	}
}