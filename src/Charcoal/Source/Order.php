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
    * @param array|\ArrayAccess $data The order data.
    * @return Order Chainable
    */
    public function setData($data)
    {
        if (isset($data['property'])) {
            $this->setProperty($data['property']);
        }
        if (isset($data['mode'])) {
            $this->setMode($data['mode']);
        }
        if (isset($data['values'])) {
            $this->setValues($data['values']);
        }
        if (isset($data['active'])) {
            $this->setActive($data['active']);
        }

        return $this;
    }

    /**
    * @param string $property The order property.
    * @throws InvalidArgumentException If the property argument is not a string.
    * @return Order (Chainable)
    */
    public function setProperty($property)
    {
        if (!is_string($property)) {
            throw new InvalidArgumentException(
                'Order Property must be a string.'
            );
        }
        if ($property == '') {
            throw new InvalidArgumentException(
                'Order Property can not be empty.'
            );
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
    * @param string $mode The order mode.
    * @throws InvalidArgumentException If the mode is not a string or invalid.
    * @return Order Chainable
    */
    public function setMode($mode)
    {
        if (!is_string($mode)) {
            throw new InvalidArgumentException(
                'Order Mode must be a string.'
            );
        }

        $mode = strtolower($mode);
        if (!in_array($mode, $this->validModes())) {
            throw new InvalidArgumentException(
                sprintf('Invalid Order mode "%s".', $mode)
            );
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
    * @throws InvalidArgumentException If the parameter is not an array or a string.
    * @param  string|array $values The order values.
    * @return Order (Chainable)
    */
    public function setValues($values)
    {
        if (is_string($values)) {
            if ($values == '') {
                throw new InvalidArgumentException(
                    'String values can not be empty.'
                );
            }
            $values = array_map('trim', explode(',', $values));
            $this->values = $values;
        } elseif (is_array($values)) {
            if (empty($values)) {
                throw new InvalidArgumentException(
                    'Array values can not be empty.'
                );
            }
            $this->values = $values;
        } else {
            throw new InvalidArgumentException(
                'Order Values must be an array, or a comma-delimited string.'
            );
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
    * @param boolean $active The active flag.
    * @return Order (Chainable)
    */
    public function setActive($active)
    {
        $this->active = !!$active;
        return $this;
    }

    /**
    * @return boolean
    */
    public function active()
    {
        return $this->active;
    }

    /**
    * Supported operators
    *
    * @return array
    */
    protected function validModes()
    {
        $validModes = [
            self::MODE_DESC,
            self::MODE_ASC,
            self::MODE_RANDOM,
            self::MODE_VALUES
        ];

        return $validModes;
    }
}
