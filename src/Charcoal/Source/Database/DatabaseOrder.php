<?php

namespace Charcoal\Source\Database;

use UnexpectedValueException;

// From 'charcoal-core'
use Charcoal\Source\Database\DatabaseExpressionInterface;
use Charcoal\Source\DatabaseSource;
use Charcoal\Source\Order;

/**
 * SQL Order Expression
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
            $mode = (string)$this->mode();
            switch ($mode) {
                /** NULL Mode */
                case '':
                    if ($this->hasCondition()) {
                        return $this->byCondition();
                    }

                    if ($this->hasValues()) {
                        return $this->byValues();
                    }
                    break;

                case self::MODE_RANDOM:
                    return $this->byRandom();

                case self::MODE_VALUES:
                    return $this->byValues();

                case self::MODE_CUSTOM:
                    return $this->byCondition();

                case self::MODE_ASC:
                case self::MODE_DESC:
                    return $this->byDirection();
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
     * Retrieve the ORDER BY clause for the direction mode.
     *
     * @throws UnexpectedValueException If any required property is empty.
     * @return string
     */
    protected function byDirection()
    {
        $fields = $this->fieldIdentifiers();
        if (empty($fields)) {
            throw new UnexpectedValueException(
                'Property is required.'
            );
        }

        $dir = $this->direction();
        $clauses = [];
        foreach ($fields as $fieldName) {
            $clauses[] = sprintf('%s %s', $fieldName, $dir);
        }

        return implode(', ', $clauses);
    }

    /**
     * Retrieve a SQL direction.
     *
     * @return string Returns "ASC" if the mode is {@see self::MODE_ASC},
     *     otherwise "DESC".
     */
    public function direction()
    {
        return ($this->mode() === self::MODE_ASC) ? 'ASC' : 'DESC';
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
        $fieldName = $this->fieldIdentifier();
        if (empty($fieldName)) {
            throw new UnexpectedValueException(
                'Property is required.'
            );
        }

        $values = $this->values();
        if (empty($values)) {
            throw new UnexpectedValueException(
                'Values can not be empty.'
            );
        }

        $values = $this->prepareValues($values);

        return sprintf('FIELD(%1$s, %2$s)', $fieldName, implode(',', $values));
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
