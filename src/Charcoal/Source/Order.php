<?php

namespace Charcoal\Source;

use InvalidArgumentException;

// From 'charcoal-core'
use Charcoal\Source\Expression;
use Charcoal\Source\ExpressionFieldTrait;
use Charcoal\Source\OrderInterface;

/**
 * Order Expression
 *
 * For sorting the results of a query.
 *
 * Available pre-defined modes:
 * - `asc` to order in ascending (0-9, A-Z) order.
 * - `desc` to order in descending (Z-A / 9-0) order.
 * - `rand` to order in a random fashion.
 * - `values` to order by a defined array of properties.
 * - `custom` to order by a custom SQL string.
 */
class Order extends Expression implements
    OrderInterface
{
    use ExpressionFieldTrait;

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
     * Set the sorting expression data.
     *
     * @param  array<string,mixed> $data The expression data;
     *     as an associative array.
     * @return self
     */
    public function setData(array $data)
    {
        parent::setData($data);

        /** @deprecated */
        if (isset($data['string'])) {
            trigger_error(
                sprintf(
                    'Sort expression option "string" is deprecated in favour of "condition": %s',
                    $data['string']
                ),
                E_USER_DEPRECATED
            );
            $this->setCondition($data['string']);
        }

        /** @deprecated */
        if (isset($data['table_name'])) {
            trigger_error(
                sprintf(
                    'Sort expression option "table_name" is deprecated in favour of "table": %s',
                    $data['table_name']
                ),
                E_USER_DEPRECATED
            );
            $this->setTable($data['table_name']);
        }

        if (isset($data['table'])) {
            $this->setTable($data['table']);
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

        if (isset($data['condition']) || isset($data['string'])) {
            if (!isset($data['mode'])) {
                $this->setMode(self::MODE_CUSTOM);
            }
        }

        return $this;
    }

    /**
     * Retrieve the default values for sorting.
     *
     * @return array<string,mixed> An associative array.
     */
    public function defaultData()
    {
        return [
            'property'  => null,
            'table'     => null,
            'mode'      => null,
            'values'    => null,
            'condition' => null,
            'active'    => true,
            'name'      => null,
        ];
    }

    /**
     * Retrieve the order clause structure.
     *
     * @return array<string,mixed> An associative array.
     */
    public function data()
    {
        return [
            'property'  => $this->property(),
            'table'     => $this->table(),
            'mode'      => $this->mode(),
            'values'    => $this->values(),
            'condition' => $this->condition(),
            'active'    => $this->active(),
            'name'      => $this->name(),
        ];
    }

    /**
     * Set the, pre-defined, sorting mode.
     *
     * @param  string|null $mode The sorting mode.
     * @throws InvalidArgumentException If the mode is not a string or invalid.
     * @return self
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
     * @return self
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
