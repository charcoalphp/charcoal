<?php

namespace Charcoal\Source;

// Dependencies from `PHP`
use \InvalidArgumentException;

// Local namespace dependencies
use \Charcoal\Source\OrderInterface;

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
    protected $property;

    /**
    * Can be 'asc', 'desc', 'rand' or 'values'
    * @var string $mode
    */
    protected $mode;

    /**
    * If $mode is "values"
    * @var array $values
    */
    protected $values;

    /**
    * @var boolean $active
    */
    protected $active = true;

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
            $this->set_values($data['values']);
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
        if (!in_array($mode, $this->valid_modes())) {
            throw new InvalidArgumentException('Invalid mode.');
        }
        $this->mode = $mode;
        return $this;
    }

    /**
    * @return string
    */
    public function mode()
    {
        return $this->mode;
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
            $this->values = $values;
        } elseif (is_array($values)) {
            if (empty($values)) {
                throw new InvalidArgumentException('Array values can not be empty.');
            }
            $this->values = $values;
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
        return $this->values;
    }

    /**
    * @param boolean $active
    * @return Order (Chainable)
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
    protected function valid_modes()
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
