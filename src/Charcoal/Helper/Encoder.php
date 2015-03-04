<?php

namespace Charcoal\Helper;

use \Charcoal\Charcoal as Charcoal;

/**
* Charcoal Encoding Utilities
*
* A simple class that allows to "encrypt" and "decrypt" strings.
*
* Note that many type of the methods used to encode / decode are NOT actual encryption and
* should not be used for storing sensitive information.
*
* # Available types
* - `base64`
*
* # How to use
* ```
* $string = 'password123';
* $salt = 'salt123';
* $cipher = new \Charcoal\Helper\Encoder('base64');
* $encoded = $cipher->encode($string, $salt);
* // ...
* $decoded = $cipher->decode($encoded, $salt);
* ```
*
* @category Charcoal
* @package Core
* @subpackage Utilities
*
* @author Mathieu Ducharme <mat@locomotive.ca>
* @copyright 2015 Locomotive
* @license LGPL <https://www.gnu.org/licenses/lgpl.html>
* @version 2015-01-29
* @link http://charcoal.locomotive.ca
* @since Version 2015-01-29
*/
class Encoder
{
	const TYPE_BASE64 = 'base64';
	const DEFAULT_TYPE = self::TYPE_BASE64;

	/**
	* Type of encoding to use.
	* Currently only support base64.
	* @var string $_type;
	*/
	private $_type = self::DEFAULT_TYPE;

	/**
	* @param string $type;
	* @throws \InvalidArgumentException if the encoding type is not valid
	*/
	public function __construct($type=null)
	{
		if($type === null) {
			// @todo Load from Charcoal::$config
			$type = self::DEFAULT_TYPE;
		}

		if(!in_array($type, $this->available_types())) {
			throw new \InvalidArgumentException('Type is not a valid encoding type');
		}

		$this->_type = $type;
	}

	/**
	*
	*/
	public function available_types()
	{
		$types = [
			self::TYPE_BASE64
		];
		return $types;
	}

	/**
	* Generate a key (merged with global Charcoal salt)
	*
	* @param string Optional extra salt
	*
	* @return string
	*/
	private function key($salt='')
	{
		$global_salt = isset(Charcoal::$config['salt']) ? Charcoal::$config['salt'] : '';
		return md5($global_salt.$salt);
	}
	
	/**
	* @param string $plain_string
	*
	* @throws \InvalidArgumentException if the parameter is not a string
	* @return string The encoded string
	*/
	public function encode($plain_string, $salt='')
	{
		if(!is_string($plain_string)) {
			throw new \InvalidArgumentException('Plain string must be a string');
		}
		$key = $this->key($salt);
		if($this->_type == self::TYPE_BASE64) {
			$encoded = '';
			$length = strlen($plain_string);
			for($i=0; $i<$length; $i++) {
				$char = substr($plain_string, $i, 1);
				$keychar = substr($key, (($i % strlen($key))-1), 1);
				$char = chr(ord($char)+ord($keychar));
				$encoded .= $char;
			}
			return base64_encode($encoded);
		}
	}

	/**
	* @throws \InvalidArgumentException if the parameter is not a string
	* @return string The decoded string
	*/
	public function decode($encoded_string, $salt='')
	{
		if(!is_string($encoded_string)) {
			throw new \InvalidArgumentException('Plain string must be a string');
		}
		$key = $this->key($salt);
		if($this->_type == self::TYPE_BASE64) {
			$decoded = '';
			$string = base64_decode($encoded_string);
			$length = strlen($string);
			for($i=0; $i<$length; $i++) {
				$char = substr($string, $i, 1);
				$keychar = substr($key, (($i % strlen($key))-1), 1);
				$char = chr(ord($char)-ord($keychar));
				$decoded .= $char;
			}
			return $decoded;
		}
	}

}