<?php
/**
 * Charcoal config class file
 *
 * @category Charcoal
 * @package Charcoal.Core
 * @subpackage Utilities
 *
 * @author Mathieu Ducharme <mat@locomotive.ca>
 * @copyright 2012-2014 Locomotive
 * @license LGPL <https://www.gnu.org/licenses/lgpl.html>
 * @version 2014-07-24
 * @link http://charcoal.locomotive.ca
 * @since Version 2012-03-01
 */

namespace Charcoal\Model;

use \Charcoal\Charcoal as Charcoal;
use \Charcoal\Helper\Cache as Cache;

/**
 * Charcoal config class
 *
 * The `Charcoal\Model\Config` object implements ArrayAccess so it's properties can be
 * accessed just like an array().
 *
 * This class holds a Model configuration, which represents thewhich typically comes from JSON files or the DB config.
 *
 * It holds both the main `Charcoal::$config` global configuration and all the Charcoal_Base
 * (Charcoal_Object) object configuration.
 *
 * ## The global config
 *
 * ## Model config
 * 
 * ## Configuration Loader
 *
 *
 * @category Charcoal
 * @package Charcoal.Core
 * @subpackage Utilities
 *
 * @author Mathieu Ducharme <mat@locomotive.ca>
 * @copyright 2012-2014 Locomotive
 * @license LGPL <https://www.gnu.org/licenses/lgpl.html>
 * @version 2012-07-24
 * @link http://charcoal.locomotive.ca
 * @since Version 2012-03-01
 */
class Metadata implements \ArrayAccess
{
	/**
	 * Can be object, property, template, email etc.
	 * @var string $config_type
	 */
	public $config_type;
	/**
	 * Versioning will allow future backward-compatibility to be kept.
	 * As of 2015-02-20 this is not yet used.
	 * @var mixed $config_version
	 */
	public $config_version;

	/**
	 * @var string $ident
	 */
	public $ident;

	/**
	 * The name of the configuration
	 * - l10n array (strings with langs as keys)
	 * @var array $name
	 */
	public $name;
	/**
	 * Description
	 * - l10n array (strings with langs as keys)
	 * @var array $description
	 */
	public $description;
	/**
	 * Longer description
	 * - l10n array (strings with langs as keys)
	 * @var array $long_description
	 */
	public $long_description;
	/**
	 * - l10n array (strings with langs as keys)
	 * @var array $notes
	 */
	public $notes;

	/**
	 * Holds the properties of this configuration object
	 * @var array $properties
	 */
	public $properties;

	/**
	* The actual config data
	* @var array $data
	*/
	public $data;

	public $actions;

	public $source;

	/**
	 * Static "cache" of all loaded config (hierarchy result)
	 *
	 * @access private
	 */
	private static $_config;

	/**
	 * Static "cache" of all loaded raw config file
	 *
	 * @access private
	 */
	private static $_config_sources;

	/**
	 * ArrayAccess isset(config[a])
	 */
	public function offsetExists($offset)
	{
		return isset($this->{$offset});
	}
	/**
	 * ArrayAccess config[a]
	 */
	public function offsetGet($offset)
	{
		return isset($this->{$offset}) ? $this->{$offset} : null;
	}

	/**
	* ArrayAccess config[a] = '';
	* @throws \InvalidArgumentException if the offset is not set ($config[] = '')
	*/
	public function offsetSet($offset, $value)
	{
		if(empty($offset)) {
			throw new \InvalidArgumentException('Offset is required');
		}
		$this->{$offset} = $value;
	}
	/**
	 *  ArrayAcces unset(config[a])
	 */
	public function offsetUnset($offset)
	{
		$this->{$offset} = null;
		unset($this->{$offset});
	}

	/**
	 * @param array
	 *
	 * @throws \InvalidArgumentException if the data parameter is not an array
	 * @return Metadata (Chainable)
	 */
	public function set_data($data)
	{
		if(!is_array($data)) {
			throw new \InvalidArgumentException('Data parameter must be an array');
		}

		foreach($data as $k => $v) {
			$this->{$k} = $v;
		}

		return $this;
	}

	/**
	* Load the configuration from the name / ident.
	*
	* ## Configuration hierarchy
	* A very important concept in Charcoal is that every config can be extended from / merged with other configs.
	* In the case of class config, the hierarchy is built automatically, in other cases it can be specified explicitely with the 2nd parameter.
	*
	* ## Classes
	* If the first parameter, $config_name, is an existing PHP class, then the hierarchy will be built automatically
	* from the class' parents. In other words, if class A extends B, class B config will be loaded then merged with class A config.
	* Traits (available since PHP 5.4) used by the class will also be extended unto the class config. Trait config should therefore always
	* be minimal to avoid conflicts in name or other concepts.
	* Note that the second parameter $hierarchy is __ignored__ when in class mode.
	*
	* ## Non-class configuration
	* It is also possible to load custom configuration that are not linked to a specific PHP class. In that cases, the hierarchy array
	* needs to be explicitely specified in the second parameter, $hierarchy.
	*
	* ## Config Sources
	* Config can be stored in many different formats. 2 are supported for now:
	* - __db__, if Charcoal::$config['config_sources']['database']
	* - __json__, always enabled, to use the filesystem + JSON files
	*
	* ## Config cache
	* Parsing all config sources JSON and merging them can be an expensive process. Therefore, loaded configuration is stored in the cache to be reused
	* This means that any changes to a configuration JSON file or the DB table will not be applied automatically if the cache is enabled; clearing the cache is required.
	* If the cache system is disabled, a static copy will also be kept to at-least prevent reloading the same cache twice for a single request.
	*
	* ## Changelog
	* - 2014-08-28: Load traits config for class config
	*
	* ## TODOs
	* - Port the cache function to Charcoal_Cache
	*
	* @param string $config_name Identifier of the configuration to load
	* @param array $hierarchy If the config_name is not a class, then it will use this hierachy of config ident
	*
	* @return Charcoal_Config (chainable)
	* @version 2014-08-28
	*
	* @see self::_load_config_source()
	*/
	public function load($config_name, $hierarchy=[])
	{
		
		$cached = Cache::get()->fetch('config_'.$config_name);
		if($cached !== false) {
			self::$_config[$config_name] = $cached;
		}

		if(isset(self::$_config[$config_name])) {
			foreach(self::$_config[$config_name] as $k => $v) {
				$this->{$k} = $v;
			}
			//pre('Used cache: '.$config_name);
			return self::$_config[$config_name];
		}

		// Get full config hierarchy
		if(class_exists($config_name)) {
			// If the object is a class, we use hierarchy from object ancestor classes
		//	pre('=='.$config_name);
			$p = $config_name;
			$config_hierarchy = [$p];

			// Also load class' traits, if any
			$traits = class_uses($config_name);
			foreach($traits as $trait) {
				$config_hierarchy[] = $trait;
			}
			//pre($p);
			while($p = get_parent_class($p)) {
				$config_hierarchy[] = $p;

				// Also load parent classes' traits, if any
				$traits = class_uses($p);
				foreach($traits as $trait) {
					//pre($trait);
					$config_hierarchy[] = $trait;
				}
			}
			
			$config_hierarchy = array_reverse($config_hierarchy);
		}
		else {
			// If the objet is NOT a class, we use the hierarchy argument to find ancestors
		//	pre('++'.$config_name);
			if(is_array($hierarchy) && !empty($hierarchy)) {
				$hierarchy[] = $config_name;
				$config_hierarchy = $hierarchy;
			}
			else {
				// No hierarchy (This configuration only) so the only item in the array
				$config_hierarchy = [$config_name];
			}
		}

		//pre($config_hierarchy);

		// Load config for full hierarchy
		$config = [];
		foreach($config_hierarchy as $c_name) {
			$c_config = self::_load_config_source($c_name);
			$config = Charcoal::merge($config, $c_config);
		}

		// Save inside this object
		foreach($config as $k => $v) {
			$this->{$k} = $v;
		}

		// Save in satic storage and cache, if available
		self::$_config[$config_name] = $config;
		\Charcoal\Helper\Cache::get()->store('config_'.$config_name, $config);

		// Chainable (return self)
		return self::$_config[$config_name];
	}

	/**
	 * Load a configuration from all its possible sources
	 *
	 * This function will do the following operations:
	 * - Normalize the $config_name parameter to set filename and keys
	 * - Attempt to load from the static variable
	 * - Attempt to load the cache
	 * - Load from the various sources
	 *   - JSON file
	 *   - DB configuration
	 * - Merge the source, in that order
	 * -
	 *
	 * @param $config_name The name of the configuration to load.
	 *
	 * @see _load_config_json()
	 * @see _load_config_db()
	 *
	 * @access private
	 * @todo 2012-06-28: DOC!
	 */
	static private function _load_config_source($config_name)
	{
		//pre(Charcoal::$config);
		// @todo: Use cache in addition to static var
		$filename = strtolower(str_replace(['_', '\\'], '.', $config_name));
		//pre($filename);
		$cache_key = 'config_source_'.$filename;

		if(isset(self::$_config_sources[$filename])) {
			return self::$_config_sources[$filename];
		}

		$cached = \Charcoal\Helper\Cache::get()->fetch($cache_key);
		if($cached !== false) {
			return $cached;
		}

		// If the global "config_sources.database is unset or false, do not use the DB for config source
		$db_inactive = (!isset(Charcoal::$config['config_sources']) || !isset(Charcoal::$config['config_sources']['database']) || !Charcoal::$config['config_sources']['database']);


		// Load JSON config
		$json_config = self::_load_config_json($filename);

		if(!$db_inactive) {

			// Load DB config
			$db_config = self::_load_config_db($filename);

			// Merge JSON config with DB config and save as static
			self::$_config_sources[$filename] = array_merge_recursive_overwrite($json_config, $db_config);
		}
		else {
			// Save as static (Do not load from DB as it was not set in config)
			self::$_config_sources[$filename] = $json_config;
		}

		// Save in cache
		\Charcoal\Helper\Cache::get()->store($cache_key, self::$_config_sources[$filename]);

		return self::$_config_sources[$filename];

	}

	/**
	 * Test: make the above function public
	 */
	static public function load_source($config_name)
	{
		return self::_load_config_source($config_name, $modes);
	}

	/**
	 * JSON is always enabled. if the $config_name file exists in a module, it is loaded
	 *
	 * @param string $config_name
	 *
	 * @return array
	 *
	 * @todo 2012-06-28: DOC!
	 */
	static private function _load_config_json($config_name)
	{
		$filename = $config_name.'.json';

		$config = [];
		$path_list = [];//Charcoal::get_all_path();
		// pre($path_list);
		foreach($path_list as $p) {
			if(file_exists($p.'config/'.$filename)) {
				$json = file_get_contents($p.'config/'.$filename);
				if($json) {
					Charcoal::debug([
						'level'=>'info',
						'msg'=>sprintf('Loading config "%s" from file system (%s)', $config_name, $filename)
					]);
					$c = json_decode($json, true); // true = array
					if(function_exists('json_last_error')) {
						switch (json_last_error()) {
							case JSON_ERROR_NONE:
								// No error!
								$config = array_merge_recursive_overwrite($config, $c);
							break;
							case JSON_ERROR_DEPTH:
								Charcoal::feedback('system.error', '', 'JSON file "'.$filename.'" is not valid. Maximum stack depth exceeded.');
							break;
							case JSON_ERROR_STATE_MISMATCH:
								Charcoal::feedback('system.error', '', 'JSON file "'.$filename.'" is not valid. Underflow or the modes mismatch.');
							break;
							case JSON_ERROR_CTRL_CHAR:
								Charcoal::feedback('system.error', '', 'JSON file "'.$filename.'" is not valid. Unexpected control character found');
							break;
							case JSON_ERROR_SYNTAX:
								Charcoal::feedback('system.error', '', 'JSON file "'.$filename.'" is not valid. Syntax error, malformed JSON..');
							break;
							case JSON_ERROR_UTF8:
								Charcoal::feedback('system.error', '', 'JSON file "'.$filename.'" is not valid. Malformed UTF-8 characters, possibly incorrectly encoded.');
								return false;
							//break;
							default:
								Charcoal::feedback('system.error', '', 'JSON file "'.$filename.'" is not valid. Unknown error.');
							break;
						}
					}
					else {
						if(!$c) {
							// Error invalid file
							Charcoal::feedback('system.error', '', 'JSON file "'.$filename.'" is not valid.');
						}
						$config = array_merge_recursive_overwrite($config, $c);
					}
				}
				else {
					// Warning empty file. Note that this will not be fired everytime because the cache might be active and therefore this function never called.
					Charcoal::feedback('system.warning', '', 'JSON file "'.$filename.'" is empty.');
				}
			}
		}

		return $config;
	}

	/**
	 * Attempt to load configuration from the database
	 *
	 * The database configuration should be stored in a table called "charcoal_config" with the following structure:
	 * - ident
	 * - json
	 * - active
	 * This function will attempt to create this table if it does not exist
	 *
	 * It is a performance hit, but since this should only be called if the cache was not hit this is not
	 * really a big deal.
	 *
	 * Having extra configuration stored in the database allow to have custom configuration per project, and save from the admin interface.
	 *
	 * @param string $config_name
	 *
	 * @return array Always an array, empty array on error
	 */
	static private function _load_config_db($config_name)
	{
		if(!isset(Charcoal::$config['config_sources']) || !isset(Charcoal::$config['config_sources']['database']) || !Charcoal::$config['config_sources']['database']) {
			// Do not use DB configuration
			return [];
		}

		// Keep this in memory
		static $db_enabled = null;
		if(is_null($db_enabled)) {
			$q = '
			show
				tables
			like
				\'charcoal_config\'';
			$res = db()->query($q);
			$table_exists = $res->fetchColumn(0);
			$db_enabled = !!$table_exists; // cast to bool

			if(!$db_enabled) {
				try {

					// Try enabling it by creating the needed table
					$q = '
					CREATE TABLE  `charcoal_config` (
						`ident` VARCHAR(255) NOT NULL,
						`json` TEXT NOT NULL,
						`active` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
						PRIMARY KEY (`ident`)
					) ENGINE = MYISAM';
					$res = db()->query($q);
					if($res) {
						$db_enabled = true;
					}
					else {
						Charcoal::debug([
							'level'=>'warning',
							'msg'=>'The configuration database could not be created ("CREATE TABLE" failed). Loading configuration from database will be disabled.'
						]);
					}
				}
				catch(Exception $e) {
					return [];
				}
				Charcoal::debug([
					'level'=>'info',
					'msg'=>sprintf('Loading config "%s" from file system (%s)', $config_name, $filename)
				]);
			}
		}

		if(!$db_enabled) {
			return [];
		}

		$q = '
		select
			`json`
		from
			`charcoal_config`
		where
			`ident` = :ident
		and
			`active` = 1
		limit
			1';
		Charcoal::debug(['level'=>'query', 'msg'=>$q]);
		$sth = db()->prepare($q);
		$sth->bindParam(':ident', $config_name);
		$sth->execute();

		$config_json = $sth->fetchColumn(0);
		if(!$config_json) {
			// @todo log error.
			return [];
		}
		$config = json_decode($config_json, true);
		if(!$config) {
			// @todo log error.
			// @todo use json_last_error here?
			return [];
		}

		return $config;
	}


}
