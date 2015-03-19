<?php
namespace Charcoal;

use \Slim\Slim as Slim;
use \Charcoal\Config as Config;

class Charcoal
{
	/**
	* @var Config $_config
	*/
	static private $_config = null;

	/**
	* @var \Slim\Slim $app
	*/
	static private $_app = null;

	static public function init($data=null)
	{
		self::$_config = new Config();

		if(isset($data['config'])) {
			self::set_config($data['config']);
		}

		self::$_app = new Slim([
			'mode'	=> self::config()->application_env(),
			'debug'	=> self::config()->dev_mode()
		]);


		date_default_timezone_set(self::config()->timezone());
		mb_internal_encoding('UTF-8');
	}

	/**
	* @var mixed $config
	* @throws \InvalidArgumentException if config is not a string, array or Config object
	*/
	static public function set_config($config)
	{
		if(self::$_config === null) {
			self::$config = new Config();
		}
		if(is_string($config)) {
			self::$_config->add_file($config);
		}
		else if(is_array($config)) {
			self::$_config->set_data($config);
		}
		else if($config instanceof Config) {
			self::$_config = $config;
		}
		else {
			throw new \InvalidArgumentException('Config must be a string (filename), array (config data) or Config object');
		}
	}

	static public function config()
	{
		if(self::$_config === null) {
			throw new \Exception('Config has not been set. Call Charcoal::init() first.');
		}
		return self::$_config;
	}

	static public function app()
	{
		return self::$_app;
	}

	/**
	* Rewrite the "array_merge_recursive" function to behave more like standard "array_merge" (overwrite values instead of appending them)
	*
	* From http://www.php.net/manual/en/function.array-merge-recursive.php#104145
	*
	* @param array $array1
	* @param array $array2,...
	*
	* @throws \InvalidArgumentException if there is not at least 2 arguments or any arguments are not array
	* @return array Merged array
	*/
	static public function merge()
	{
		$args = func_get_args();
		if(func_num_args() < 2) {
			throw new \InvalidArgumentException('This function takes at least two parameters');
		}

		$array_list = func_get_args();
		$result = [];

		while($array_list) {

			$current = array_shift($array_list);

			// Make sure the argument is an array. @todo: Convert objects to array??
			if(!is_array($current)) {
				throw new \InvalidArgumentException('All parameters must be arrays');
			}
			if(!$current) {
				continue;
			}

			foreach($current as $key => $value) {
				if(is_string($key)) {
					if(is_array($value) && array_key_exists($key, $result) && is_array($result[$key])) {
						$result[$key] = call_user_func([__CLASS__, __FUNCTION__], $result[$key], $value);
					}
					else {
						$result[$key] = $value;
					}
				}
				else {
					$result[] = $value;
				}
			}
		}

		return $result;
	}
}
