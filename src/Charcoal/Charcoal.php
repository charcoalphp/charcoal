<?php
namespace Charcoal;

use \Charcoal\Helper\Cache;

class Charcoal
{
	/**
	* self::$config Hold the main Charcoal configuration object / array
	* @var array $config
	*/
	static public $config = [];

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
