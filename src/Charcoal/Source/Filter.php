<?php

namespace Charcoal\Source;

// Dependencies from `PHP`
use \InvalidArgumentException;

// Local namespace dependencies
use \Charcoal\Source\FilterInterface;

/**
*
*/
class Filter implements FilterInterface
{
    const DEFAULT_OPERATOR = '=';
    const DEFAULT_FUNC     = '';
    const DEFAULT_OPERAND  = 'AND';

    /**
    * @var string $property
    */
    protected $property;
    /**
    * @var mixed $val
    */
    protected $val;

    /**
    * @var string $operator
    */
    protected $operator = self::DEFAULT_OPERATOR;
    /**
    * @var string $func
    */
    protected $func = self::DEFAULT_FUNC;
    /**
    * @var string $operand
    */
    protected $operand = self::DEFAULT_OPERAND;

    /**
    * @var string $string
    */
    protected $string;

    /**
    * Inactive filter should be skipped completely.
    * @var boolean $active
    */
    protected $active;

    /**
    * @param array $data
    * @return Filter Chainable
    */
    public function set_data(array $data)
    {
        if (isset($data['property'])) {
            $this->set_property($data['property']);
        }
        if (isset($data['val'])) {
            $this->set_val($data['val']);
        }
        if (isset($data['operator'])) {
            $this->set_operator($data['operator']);
        }
        if (isset($data['func'])) {
            $this->set_func($data['func']);
        }
        if (isset($data['operand'])) {
            $this->set_operand($data['operand']);
        }
        if (isset($data['string'])) {
            $this->set_string($data['string']);
        }
        if (isset($data['active'])) {
            $this->set_active($data['active']);
        }
        return $this;
    }

    /**
    * @param string $property
    * @throws InvalidArgumentException if the property argument is not a string
    * @return Filter (Chainable)
    */
    public function set_property($property)
    {
        if (!is_string($property)) {
            throw new InvalidArgumentException('Property must be a string.');
        }
        if ($property=='') {
            throw new InvalidArgumentException('Property can not be empty.');
        }

        $this->property = $property;
        return $this;
    }

    /**
    * @return string
    */
    public function property()
    {
        return $this->property;
    }

    /**
    * @param mixed $val
    * @return Filter (Chainable)
    */
    public function set_val($val)
    {
        $this->val = $val;
        return $this;
    }

    /**
    * @return mixed
    */
    public function val()
    {
        return $this->val;
    }

    /**
    * @param string $operator
    * @throws InvalidArgumentException if the parameter is not a valid operator
    * @return Filter (Chainable)
    */
    public function set_operator($operator)
    {
        if (!is_string($operator)) {
            throw new InvalidArgumentException('Operator should be a string.');
        }

        $operator = strtoupper($operator);
        if (!in_array($operator, $this->valid_operators())) {
            throw new InvalidArgumentException('This is not a valid operator.');
        }

        $this->operator = $operator;
        return $this;
    }

    /**
    * @return string
    */
    public function operator()
    {
        return strtoupper($this->operator);
    }

    /**
    * @param string $func
    * @throws InvalidArgumentException if the parameter is not a valid function
    * @return Filter (Chainable)
    */
    public function set_func($func)
    {
        if (!is_string($func)) {
            throw new InvalidArgumentException('Func should be astring.');
        }

        $func = strtoupper($func);
        if (!in_array($func, $this->valid_func())) {
            throw new InvalidArgumentException('This is not a valid function.');
        }
        $this->func = $func;
        return $this;
    }

    /**
    * @return string
    */
    public function func()
    {
        return $this->func;
    }

    /**
    * @param string $operand
    * @throws InvalidArgumentException if the parameter is not a valid operand
    * @return Filter (Chainable)
    */
    public function set_operand($operand)
    {
        if (!is_string($operand)) {
            throw new InvalidArgumentException('Operand should be a string.');
        }

        $operand = strtoupper($operand);
        if (!in_array($operand, $this->valid_operands())) {
            throw new InvalidArgumentException('This is not a valid operand.');
        }

        $this->operand = $operand;
        return $this;
    }

    /**
    * @return string
    */
    public function operand()
    {
        return strtoupper($this->operand);
    }

    /**
    * @param string $sql
    * @throws InvalidArgumentException if the parameter is not a valid operand
    * @return Filter (Chainable)
    */
    public function set_string($sql)
    {
        if (!is_string($sql)) {
            throw new InvalidArgumentException(
                'String should be a string.'
            );
        }

        $this->string = $sql;
        return $this;
    }

    /**
    * @return string
    */
    public function string()
    {
        return $this->string;
    }

    /**
    * @param boolean $active
    * @return Filter (Chainable)
    */
    public function set_active($active)
    {
        $this->active = $active;
        return $this;
    }

    /**
    * @return boolean
    */
    public function active()
    {
        return !!$this->active;
    }



    /**
    * Supported operators
    *
    * @return array
    */
    protected function valid_operators()
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
    * Supported operand types, uppercase
    *
    * @return array
    */
    protected function valid_operands()
    {
        $valid_operands = [
            'AND', '&&',
            'OR', '||',
            'XOR'
        ];

        return $valid_operands;
    }

    /**
    * Supported functions, uppercase
    * @return array
    */
    protected function valid_func()
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
}
