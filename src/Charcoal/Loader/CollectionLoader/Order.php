<?php

namespace Charcoal\Loader\CollectionLoader;

class Order
{
	const MODE_ASC = 'asc';
	const MODE_DESC = 'desc';
	const MODE_RANDOM = 'rand';
	const MODE_VALUES = 'values';

	private $_parent_loader;

	/**
	* @var string
	*/
	private $_property;

	/**
	* Can be 'asc', 'desc', 'rand' or 'values'
	* @var string $mode
	*/
	private $_mode;

	/**
	* If $_mode is "values"
	* @var array $values
	*/
	private $_values;

	public $active = true;

	/**
	* @param string $property
	* @throws \InvalidArgumentException if the property argument is not a string
	* @return Order (Chainable)
	*/
	public function set_property($property)
	{
		if(!is_string($property)) {
			throw new \InvalidArgumentException('Property must be a string');
		}
		if($property=='') {
			throw new \InvalidArgumentException('Property can not be empty');
		}

		$this->_property = $property;
		return $this;
	}

	public function property()
	{
		return $this->_property;
	}

	public function set_mode($mode)
	{
		if(!is_string($mode)) {
			throw new \InvalidArgumentException('Mode must be a string');
		}

		$mode = strtolower($mode);
		if(!in_array($mode, $this->_valid_modes())) {
			throw new \InvalidArgumentException('Invalid mode');
		}
		$this->_mode = $mode;
		return $this;
	}

	public function mode()
	{
		return $this->_mode;
	}

	/**
	* Set the values.
	* Values are ignored if the mode is not "values"
	*
	* If the `$values` argument is a string, it will be split by ",".
	* If it is an array, the values will be used as is.
	* Otherwise, the function will throw an error
	*
	* @throws \InvalidArgumentException if the parameter is not an array or a string
	* @param string|array $values
	* @return Order (Chainable)
	*/
	public function set_values($values)
	{
		if(is_string($values)) {
			if($values == '') {
				throw new \InvalidArgumentException('String values can not be empty');
			}
			$values = array_map('trim', explode(',', $values));
			$this->_values = $values;
		}
		else if(is_array($values)) {
			if(empty($values)) {
				throw new \InvalidArgumentException('Array values can not be empty');
			}
			$this->_values = $values;
		}
		else {
			throw new \InvalidArgumentException('Values must be an array, or a comma-delimited string');
		}
		return $this;
	}

	public function values()
	{
		return $this->_values;
	}

	/**
	* Supported operators
	*
	* @return array
	*/
	protected function _valid_modes()
	{
		$valid_modes = [
			self::MODE_DESC,
			self::MODE_ASC,
			self::MODE_RANDOM,
			self::MODE_VALUES
		];

		return $valid_modes;
	}

	/**
	* @throws \DomainException
	* @return string
	*/
	public function sql()
	{
		$property = $this->property();
		$mode = $this->mode();

		if($mode == 'rand') {
			return 'RAND()';
		}
		if($mode == 'values') {
			$values = $this->values();
			if(empty($values)) {
				throw new \DomainException('Values can not be empty');
			}
			if($property == '') {
				throw new \DomainException('Property can not be empty');
			}

			return 'FIELD(`'.$property.'`, '.implode(',', $values).')';
		}
		else {
			if($property == '') {
				throw new \DomainException('Property can not be empty');
			}
			return '`'.$property.'` '.$mode;
		}
		
	}
}