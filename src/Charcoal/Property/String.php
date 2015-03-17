<?php

namespace Charcoal\Property;

use \Charcoal\Model\Property as Property;
use \Charcoal\Model\Validator\Propertyalidator as Validator;

class String extends Property
{
	const DEFAULT_MIN_LENGTH = 0;
	const DEFAULT_MAX_LENGTH = 255;
	const DEFAULT_REGEXP = '';

	private $_min_length;
	private $_max_length;
	private $_regexp;

	/**
	* @param array $data
	* @throws \InvalidArgumentException if the parameter is not an array
	* @return String (Chainable)
	*/
	public function set_data($data)
	{

		if(!is_array($data)) {
			throw new \InvalidArgumentException('Data must be an array');
		}

		parent::set_data($data);

		if(isset($data['max_length'])) {
			$this->set_max_length($data['max_length']);
		}
		if(isset($data['min_length'])) {
			$this->set_min_length($data['min_length']);
		}
		if(isset($data['regexp'])) {
			$this->set_regexp($data['regexp']);
		}
		return $this;
	}

	/**
	* @param integer $max_length
	* @throws \InvalidArgumentException if the parameter is not an integer
	* @return String (Chainable)
	*/
	public function set_max_length($max_length)
	{
		if(!is_integer($max_length)) {
			throw new \InvalidArgumentException("Max length must be an integer.");
		}
		if($max_length < 0) {
			throw new \InvalidArgumentException("Max length must be a positive integer (>=0)");
		}
		$this->_max_length = $max_length;
		return $this;
	}

	/**
	* @return integer
	*/
	public function max_length()
	{
		if($this->_max_length === null) {
			$this->_max_length = self::DEFAULT_MAX_LENGTH;
		}
		return $this->_max_length;
	}

	/**
	* @param integer $min_length
	* @throws \InvalidArgumentException if the parameter is not an integer
	* @return String (Chainable)
	*/
	public function set_min_length($min_length)
	{
		if(!is_integer($min_length)) {
			throw new \InvalidArgumentException("Min length must be an integer.");
		}
		if($min_length < 0) {
			throw new \InvalidArgumentException("Min length must be a positive integer (>=0)");
		}
		$this->_min_length = $min_length;
		return $this;
	}

	/**
	* @return integer
	*/
	public function min_length()
	{
		if($this->_min_length === null) {
			$this->_min_length = self::DEFAULT_MIN_LENGTH;
		}
		return $this->_min_length;
	}

	/**
	* @param string $regexp
	* @throws \InvalidArgumentException if the parameter is not a string
	* @return String (Chainable)
	*/
	public function set_regexp($regexp)
	{
		if(!is_string($regexp)) {
			throw new \InvalidArgumentException("Regular expression must be a string.");
		}
		$this->_regexp = $regexp;
		return $this;
	}

	/**
	* @return string
	*/
	public function regexp()
	{
		if($this->_regexp === null) {
			$this->_regexp = self::DEFAULT_REGEXP;
		}
		return $this->_regexp;
	}

	/**
	* @todo Support l10n values
	* @todo Support multiple values
	* @throws \Exception if val is not a string
	* @return integer
	*/
	public function length()
	{
		$val = $this->val();
		if(!is_string($val)) {
			throw new \Exception('Val is not a string');
		}
		return mb_strlen($val);
	}

	/**
	* @return boolean
	*/
	public function validate_min_length()
	{
		$val = $this->val();
		$min_length = $this->min_length();
		if($min_length == 0) {
			return true;
		}
		
		$valid = (mb_strlen($val) >= $min_length);
		if(!$valid) {
			$this->validator()->error('Min length error');
		}

		return $valid;
	}

	/**
	* @return boolean
	*/
	public function validate_max_length()
	{
		$val = $this->val();
		$max_length = $this->max_length();
		if($max_length == 0) {
			return true;
		}
		
		$valid = (mb_strlen($val) <= $max_length);
		if(!$valid) {
			$this->validator()->error('Max length error');
		}

		return $valid;

	}

	/**
	* @return boolean
	*/
	public function validate_regexp()
	{
		$val = $this->val();
		$regexp = $this->regexp();
		if($regexp == '') {
			return true;
		}

		$valid = !!preg_match($regexp, $val);
		if(!$valid) {
			$this->validator()->error('Regexp error');
		}

		return $valid;
	}

	/**
	* Get the SQL type (Storage format)
	*
	* Stored as VARCHAR for max_length under 255 and TEXT for other, longer strings
	*
	* @return string The SQL type
	*/
	public function sql_type()
	{
		// Multiple strings are always stored as TEXT because they can hold multiple values
		if($this->multiple()) {
			return 'TEXT';
		}

		$max_length = $this->max_length();
		// VARCHAR or TEXT, depending on length
		if($max_length <= 255 && $max_length != 0) {
			return 'VARCHAR('.$max_length.')';
		}
		else {
			return 'TEXT';
		}
	}
}