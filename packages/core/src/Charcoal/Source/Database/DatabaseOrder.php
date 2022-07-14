<?php

namespace Charcoal\Source\Database;

use UnexpectedValueException;
// From 'charcoal-core'
use Charcoal\Source\Database\DatabaseExpressionInterface;
use Charcoal\Source\DatabaseSource;
use Charcoal\Source\Order;

/**
 * SQL Order Expression
 *
 * Priority of SQL resolution if the expression is "active":
 * 1. Random — If "mode" is set to "rand".
 * 2. Custom — If "condition" is defined or "mode" set to "custom".
 * 3. Values — If "property" and "values" are defined or "mode" set to "values".
 * 4. Direction — If "property" is defined.
 */
class DatabaseOrder extends Order implements
    DatabaseExpressionInterface
{
    /**
     * The table related to the field identifier.
     *
     * @var string
     */
    protected $table = DatabaseSource::DEFAULT_TABLE_ALIAS;

    /**
     * Retrieve the default values for sorting.
     *
     * @return array
     */
    public function defaultData()
    {
        $defaults = parent::defaultData();
        $defaults['table'] = DatabaseSource::DEFAULT_TABLE_ALIAS;

        return $defaults;
    }

    /**
     * Converts the order into a SQL expression for the ORDER BY clause.
     *
     * @return string A SQL string fragment.
     */
    public function sql()
    {
        if ($this->active()) {
            switch ($this->mode()) {
                case self::MODE_RANDOM:
                    return $this->byRandom();

                case self::MODE_CUSTOM:
                    return $this->byCondition();

                case self::MODE_VALUES:
                    return $this->byValues();
            }

            if ($this->hasCondition()) {
                return $this->byCondition();
            }

            if ($this->hasValues()) {
                return $this->byValues();
            }

            if ($this->hasProperty()) {
                return $this->byProperty();
            }
        }

        return '';
    }

    /**
     * Retrieve the ORDER BY clause for the {@see self::MODE_RANDOM} mode.
     *
     * @return string
     */
    protected function byRandom()
    {
        return 'RAND()';
    }

    /**
     * Generate the ORDER BY clause(s) for the direction mode.
     *
     * @throws UnexpectedValueException If any required property is empty.
     * @return string
     */
    protected function byProperty()
    {
        $fields = $this->fieldIdentifiers();
        if (empty($fields)) {
            throw new UnexpectedValueException(
                'Property is required.'
            );
        }

        $dir = $this->direction();
        $dir = $dir === null ? '' : ' ' . $dir;

        return implode($dir . ', ', $fields) . $dir;
    }

    /**
     * Retrieve the ORDER BY clause for the {@see self::MODE_CUSTOM} mode.
     *
     * @throws UnexpectedValueException If the custom clause is empty.
     * @return string
     */
    protected function byCondition()
    {
        if (!$this->hasCondition()) {
            throw new UnexpectedValueException(
                'Custom expression can not be empty.'
            );
        }

        return $this->condition();
    }

    /**
     * Retrieve the ORDER BY clause for the {@see self::MODE_VALUES} mode.
     *
     * @throws UnexpectedValueException If any required property or values is empty.
     * @return string
     */
    protected function byValues()
    {
        $fields = $this->fieldIdentifiers();
        if (empty($fields)) {
            throw new UnexpectedValueException(
                'Property is required.'
            );
        }

        $values = $this->prepareValues($this->values());
        if (empty($values)) {
            throw new UnexpectedValueException(sprintf(
                'Value can not be empty on fields: %s',
                implode(', ', $fields)
            ));
        }

        $dir = $this->direction();
        $dir = $dir === null ? '' : ' ' . $dir;

        $values  = implode(',', $values);
        $clauses = [];
        foreach ($fields as $fieldName) {
            $clauses[] = sprintf('FIELD(%1$s, %2$s)', $fieldName, $values) . $dir;
        }

        return implode(', ', $clauses);
    }

    /**
     * Parse the given values for SQL.
     *
     * @param  mixed $values The value to be normalized.
     * @return array Returns a collection of parsed values.
     */
    public function prepareValues($values)
    {
        if (empty($values)) {
            return [];
        }

        if (!is_array($values)) {
            $values = (array)$values;
        }

        $values = array_filter($values, 'is_scalar');
        $values = array_map('self::quoteValue', $values);

        return $values;
    }
}
