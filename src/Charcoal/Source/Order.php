<?php

namespace Charcoal\Source;

// Dependencies from `PHP`
use \DomainException as DomainException;
use \InvalidArgumentException as InvalidArgumentException;

// Local namespace dependencies
use \Charcoal\Source\OrderInterface as OrderInterface;

/**
*
*/
class Order implements OrderInterface
{
    const MODE_ASC = 'asc';
    const MODE_DESC = 'desc';
    const MODE_RANDOM = 'rand';
    const MODE_VALUES = 'values';

    /**
    * @var string
    */
    protected $_property;

    /**
    * Can be 'asc', 'desc', 'rand' or 'values'
    * @var string $mode
    */
    protected $_mode;

    /**
    * If $_mode is "values"
    * @var array $values
    */
    protected $_values;

    /**
    * @var boolean $_active
    */
    protected $_active = true;

    /**
    * @param array $data
    * @return Order Chainable
    */
    public function set_data(array $data)
    {
        if (isset($data['property'])) {
            $this->set_property($data['property']);
        }
        if (isset($data['mode'])) {
            $this->set_mode($data['mode']);
        }
        if (isset($data['values'])) {
            $this->set_operator($data['values']);
        }
        if (isset($data['operand'])) {
            $this->set_operand($data['operand']);
        }
        if (isset($data['sql'])) {
            $this->set_sql($data['sql']);
        }
        if (isset($data['active'])) {
            $this->set_active($data['active']);
        }

        return $this;
    }

    /**
    * @param string $property
    * @throws InvalidArgumentException if the property argument is not a string
    * @return Order (Chainable)
    */
    public function set_property($property)
    {
        if (!is_string($property)) {
            throw new InvalidArgumentException('Property must be a string.');
        }
        if ($property=='') {
            throw new InvalidArgumentException('Property can not be empty.');
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
    * @param string $mode
    * @throws InvalidArgumentException
    * @return Order Chainable
    */
    public function set_mode($mode)
    {
        if (!is_string($mode)) {
            throw new InvalidArgumentException('Mode must be a string.');
        }

        $mode = strtolower($mode);
        if (!in_array($mode, $this->_valid_modes())) {
            throw new InvalidArgumentException('Invalid mode.');
        }
        $this->_mode = $mode;
        return $this;
    }

    /**
    * @return string
    */
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
    * @throws InvalidArgumentException if the parameter is not an array or a string
    * @param  string|array $values
    * @return Order (Chainable)
    */
    public function set_values($values)
    {
        if (is_string($values)) {
            if ($values == '') {
                throw new InvalidArgumentException('String values can not be empty.');
            }
            $values = array_map('trim', explode(',', $values));
            $this->_values = $values;
        } elseif (is_array($values)) {
            if (empty($values)) {
                throw new InvalidArgumentException('Array values can not be empty.');
            }
            $this->_values = $values;
        } else {
            throw new InvalidArgumentException('Values must be an array, or a comma-delimited string.');
        }
        return $this;
    }

    /**
    * @return array
    */
    public function values()
    {
        return $this->_values;
    }

    /**
    * @param boolean $active
    * @return Order (Chainable)
    */
    public function set_active($active)
    {
        $this->_active = $active;
        return $this;
    }

    /**
    * @return boolean
    */
    public function active()
    {
        return !!$this->_active;
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
}
