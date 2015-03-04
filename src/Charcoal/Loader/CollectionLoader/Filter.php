<?php

namespace Charcoal\Loader\CollectionLoader;

class Filter
{
	const DEFAULT_OPERATOR = '=';
	const DEFAULT_FUNC = '';
	const DEFAULT_OPERAND = 'AND';

	/**
	* @var string $_property
	*/
	private $_property;
	/**
	* @var mixed $_val
	*/
	private $_val;

	/**
	* @var string $_operator
	*/
	private $_operator = self::DEFAULT_OPERATOR;
	/**
	* @var string $_func
	*/
	private $_func = self::DEFAULT_FUNC;
	/**
	* @var string $_operand
	*/
	private $_operand = self::DEFAULT_OPERAND;

	/**
	* @param string $property
	* @throws \InvalidArgumentException if the property argument is not a string
	* @return Filter (Chainable)
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

	/**
	* @return string
	*/
	public function property()
	{
		return $this->_property;
	}

	/**
	* @param mixed $val
	* @return Filter (Chainable)
	*/
	public function set_val($val)
	{
		$this->_val = $val;
		return $this;
	}

	/**
	* @return mixed
	*/
	public function val()
	{
		return $this->_val;
	}

	/**
	* @param string $operator
	* @throws \InvalidArgumentException if the parameter is not a valid operator
	* @return Filter (Chainable)
	*/
	public function set_operator($operator)
	{
		if(!is_string($operator)) {
			throw new \InvalidArgumentException('Operator should be a string');
		}

		$operator = strtoupper($operator);
		if(!in_array($operator, $this->_valid_operators())) {
			throw new \InvalidArgumentException('This is not a valid operator.');
		}

		$this->_operator = $operator;
		return $this;
	}

	/**
	* @return string
	*/
	public function operator()
	{
		return strtoupper($this->_operator);
	}

	/**
	* @param string $func
	* @throws \InvalidArgumentException if the parameter is not a valid function
	* @return Filter (Chainable)
	*/
	public function set_func($func)
	{
		if(!is_string($func)) {
			throw new \InvalidArgumentException('Func should be astring');
		}

		$func = strtoupper($func);
		if(!in_array($func, $this->_valid_func())) {
			throw new \InvalidArgumentException('This is not a valid function.');
		}
		$this->_func = $func;
		return $this;
	}

	/**
	* @return string
	*/
	public function func()
	{
		return $this->_func;
	}

	/**
	* @param string $operand
	* @throws \InvalidArgumentException if the parameter is not a valid operand
	* @return Filter (Chainable)
	*/
	public function set_operand($operand)
	{
		if(!is_string($operand)) {
			throw new \InvalidArgumentException('Operand should be a string.');
		}

		$operand = strtoupper($operand);
		if(!in_array($operand, $this->_valid_operands())) {
			throw new \InvalidArgumentException('This is not a valid operand.');
		}

		$this->_operand = $operand;
		return $this;
	}

	/**
	* @return string
	*/
	public function operand()
	{
		return strtoupper($this->_operand);
	}

	public function sql()
	{
		$property = $this->property();
		if(!$property) {
			return '';
		}
		$val = $this->val();

		// Support custom "operator" for the filter
		$operator = $this->operator();

		// Support for custom function on column name
		$function = $this->func();

		// Support for custom operand (@todo)
		// @todo: Should this be in this function at all??
		$operand = $this->operand();

		if($function) {
			$target = $function.'(`'.$property.'`)';
		}
		else {
			$target = '`'.$property.'`';
		}

		switch($operator) {
			/*case '=':

				if($this->multiple() && ($sql_val != "''")) {
					$sep = isset($this->multiple_options['separator']) ? $this->multiple_options['separator'] : ',';
					if($sep == ',') {
						$filter = ' FIND_IN_SET('.$sql_val.', '.$filter_ident.')';
					}
					else {
						// The FIND_IN_SET function must work on a comma separated-value. So create temporary separators to use a comma...
						$custom_separator = '}x5S_'; // With not much luck, this string should never be used in text
						$filter = ' FIND_IN_SET(REPLACE('.$sql_val.', \',\', \''.$custom_separator.'\'), REPLACE(REPLACE('.$filter_ident.', \',\', \''.$custom_separator.'\'), \''.$sep.'\', \',\')';
					}
				}
				else {
					$filter = '('.$filter_ident.' '.$operator.' '.$sql_val.')';
				}
			break;
			*/

			case 'IS NULL':
			case 'IS NOT NULL':
				$filter = '('.$target.' '.$operator.')';
			break;

			default:
				$filter = '('.$target.' '.$operator.' '.$val.')';
			break;
		}

		return $filter;
	}

	/**
	* Supported operators
	*
	* @return array
	*/
	protected function _valid_operators()
	{
		$valid_operators = [
			'=', 'IS', '!=', 'IS NOT',
			'LIKE', 'NOT LIKE',
			'>', '>=', '<', '<=',
			'IS NULL', 'IS NOT NULL',
			'%', 'MOD',
			'REGEXP', 'NOT REGEXP'
		];

		return $valid_operators;
	}

	/**
	* Supported functions
	* @return array
	*/
	protected function _valid_func()
	{
		$valid_functions = [
			'ABS',
			'ACOS', 'ASIN', 'ATAN',
			'COS', 'COT', 'SIN', 'TAN',
			'CEIL', 'CEILING', 'FLOOR', 'ROUND',
			'CHAR_LENGTH', 'CHARACTER_LENGTH', 'LENGTH', 'OCTET_LENGTH',
			'CRC32', 'MD5', 'SHA1',
			'DATE', 
			'DAY', 'DAYNAME', 'DAYOFMONTH', 'DAYOFWEEK', 'DAYOFYEAR', 'LAST_DAY',
			'MONTH', 'MONTHNAME', 
			'WEEK', 'WEEKDAY', 'WEEKOFYEAR', 'YEARWEEK',
			'YEAR', 
			'QUARTER', 
			'FROM_UNIXTIME',
			'HOUR', 'MICROSECOND', 'MINUTE', 'SECOND', 'TIME',
			'TIMESTAMP', 'UNIX_TIMESTAMP',
			'DEGREES', 'RADIANS',
			'EXP', 'LOG', 'LOG10', 'LN',
			'HEX',
			'LCASE', 'LOWER', 'UCASE', 'UPPER',
			'LTRIM', 'RTRIM', 'TRIM',
			'REVERSE',
			'SIGN',
			'SQRT'
		];

		return $valid_functions;
	}

	/**
	* Supported operand types
	** @return array
	*/
	protected function _valid_operands()
	{
		$valid_operands = [
			'AND', '&&',
			'OR', '||',
			'XOR'
		];

		return $valid_operands;
	}
}