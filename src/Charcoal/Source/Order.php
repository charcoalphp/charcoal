<?php

namespace Charcoal\Source;

use InvalidArgumentException;

// From 'charcoal-core'
use Charcoal\Source\AbstractExpression;
use Charcoal\Source\FieldTrait;
use Charcoal\Source\OrderInterface;

/**
 * Order Expression
 *
 * Available pre-defined modes:
 * - `asc` to order in ascending (0-9, A-Z) order.
 * - `desc` to order in descending (Z-A / 9-0) order.
 * - `rand` to order in a random fashion.
 * - `values` to order by a defined array of properties.
 * - `custom` to order by a custom SQL string.
 */
class Order extends AbstractExpression implements
    OrderInterface
{
    use FieldTrait;

    const MODE_ASC    = 'asc';
    const MODE_DESC   = 'desc';
    const MODE_RANDOM = 'rand';
    const MODE_VALUES = 'values';
    const MODE_CUSTOM = 'custom';

    /**
     * The sort mode.
     *
     * @var string|null
     */
    protected $mode;

    /**
     * The values to sort against when {@see self::$mode} is {@see self::MODE_VALUES}.
     *
     * @var array|null
     */
    protected $values;

    /**
     * Set the order clause data.
     *
     * @param  array $data The clause data.
     * @return Order Chainable
     */
    public function setData(array $data)
    {
        parent::setData($data);

        if (isset($data['table_name'])) {
            $this->setTableName($data['table_name']);
        }

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
            if (!isset($data['mode'])) {
                $this->setMode(self::MODE_CUSTOM);
            }
        }

        return $this;
    }

    /**
     * Retrieve the default values for sorting.
     *
     * @return array<string,mixed>
     */
    public function defaultData()
    {
        return [
            'property'   => null,
            'table_name' => null,
            'mode'       => null,
            'values'     => null,
            'string'     => null,
            'active'     => true,
            'name'       => null,
        ];
    }

    /**
     * Retrieve the order clause structure.
     *
     * @return array<string,mixed>
     */
    public function data()
    {
        $data = [
            'property'   => $this->property(),
            'table_name' => $this->tableName(),
            'mode'       => $this->mode(),
            'values'     => $this->values(),
            'string'     => $this->string(),
            'active'     => $this->active(),
            'name'       => $this->name(),
        ];

        return array_udiff_assoc($data, $this->defaultData(), [ $this, 'diffValues' ]);
    }

    /**
     * Set the, pre-defined, sorting mode.
     *
     * @param  string|null $mode The order mode.
     * @throws InvalidArgumentException If the mode is not a string or invalid.
     * @return Order Chainable
     */
    public function setMode($mode)
    {
        if ($mode === null) {
            $this->mode = $mode;
            return $this;
        }

        if (!is_string($mode)) {
            throw new InvalidArgumentException(
                'Order Mode must be a string.'
            );
        }

        $mode  = strtolower($mode);
        $valid = $this->validModes();
        if (!in_array($mode, $valid)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid Order Mode. Must be one of "%s"',
                implode('", "', $valid)
            ));
        }

        $this->mode = $mode;

        return $this;
    }

    /**
     * Retrieve the sorting mode.
     *
     * @return string|null
     */
    public function mode()
    {
        return $this->mode;
    }

    /**
     * Set the values to sort against.
     *
     * Note: Values are ignored if the mode is not {@see self::MODE_VALUES}.
     *
     * @throws InvalidArgumentException If the parameter is not an array or a string.
     * @param  string|array|null $values A list of field values.
     *     If the $values parameter:
     *     - is a string, the string will be split by ",".
     *     - is an array, the values will be used as is.
     *     - any other data type throws an exception.
     * @return Order Chainable
     */
    public function setValues($values)
    {
        if ($values === null) {
            $this->values = $values;
            return $this;
        }

        if (is_string($values)) {
            if ($values === '') {
                throw new InvalidArgumentException(
                    'String values can not be empty.'
                );
            }

            $values = array_map('trim', explode(',', $values));
        }

        if (is_array($values)) {
            if (empty($values)) {
                throw new InvalidArgumentException(
                    'Array values can not be empty.'
                );
            }

            $this->values = $values;
            return $this;
        }

        throw new InvalidArgumentException(sprintf(
            'Order Values must be an array or comma-delimited string, received %s',
            is_object($values) ? get_class($values) : gettype($values)
        ));
    }

    /**
     * Determine if the Order expression has values.
     *
     * @return boolean
     */
    public function hasValues()
    {
        return !empty($this->values);
    }

    /**
     * Retrieve the values to sort against.
     *
     * @return array|null A list of field values or NULL if no values were assigned.
     */
    public function values()
    {
        return $this->values;
    }

    /**
     * Retrieve the supported sorting modes.
     *
     * @return array
     */
    protected function validModes()
    {
        return [
            self::MODE_DESC,
            self::MODE_ASC,
            self::MODE_RANDOM,
            self::MODE_VALUES,
            self::MODE_CUSTOM
        ];
    }
}
