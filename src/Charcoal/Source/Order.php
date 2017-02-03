<?php

namespace Charcoal\Source;

use \InvalidArgumentException;

// From 'charcoal-core'
use \Charcoal\Source\OrderInterface;

/**
 * Order
 *
 * Available modes:
 * - `asc` to order in ascending (A-Z / 0-9) order.
 * - `desc` to order in descending (Z-A / 9-0) order.
 * - `rand` to order in a random fashion.
 * - `values` to order by a defined array of properties.
 * - `custom` to order by a custom SQL string.
 */
class Order implements OrderInterface
{
    const MODE_ASC = 'asc';
    const MODE_DESC = 'desc';
    const MODE_RANDOM = 'rand';
    const MODE_VALUES = 'values';
    const MODE_CUSTOM = 'custom';

    /**
     * The model property (SQL column).
     *
     * @var string
     */
    protected $property;

    /**
     * The sort mode.
     *
     * @var string
     */
    protected $mode;

    /**
     * The values when {@see self::$mode} is {@see self::MODE_VALUES}.
     *
     * @var array
     */
    protected $values;

    /**
     * Raw SQL clause when {@see self::$mode} is {@see self::MODE_CUSTOM}.
     *
     * @var string
     */
    protected $string;

    /**
     * Whether the order is active.
     *
     * @var boolean
     */
    protected $active = true;

    /**
     * @param array $data The order data.
     * @return Order Chainable
     */
    public function setData(array $data)
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

        if (isset($data['string'])) {
            $this->setString($data['string']);

            if (!isset($data['mode'])) {
                $this->setMode(self::MODE_CUSTOM);
            }
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
     * @param  string $sql The custom order SQL string.
     * @throws InvalidArgumentException If the parameter is not a valid operand.
     * @return Order (Chainable)
     */
    public function setString($sql)
    {
        if (!is_string($sql)) {
            throw new InvalidArgumentException(
                'Custom SQL clause should be a string.'
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
     * Supported modes
     *
     * @return array
     */
    protected function validModes()
    {
        $validModes = [
            self::MODE_DESC,
            self::MODE_ASC,
            self::MODE_RANDOM,
            self::MODE_VALUES,
            self::MODE_CUSTOM
        ];

        return $validModes;
    }
}
