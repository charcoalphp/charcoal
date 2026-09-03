<?php

namespace Charcoal\Source\Database;

use UnexpectedValueException;
// From 'charcoal-core'
use Charcoal\Source\AbstractExpression;
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
 *
 * FIELD() list members are emitted as named PDO placeholders; use {@see binds()} with the SQL.
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
     * Named PDO parameter binds collected during {@see sql()}.
     *
     * @var array<string,mixed>
     */
    private $binds = [];

    /**
     * Process-wide counter so order placeholders never collide with each other or filters.
     *
     * @var integer
     */
    private static $bindSequence = 0;

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
     * Named PDO binds for the last {@see sql()} compilation.
     *
     * @return array<string,mixed>
     */
    public function binds()
    {
        return $this->binds;
    }

    /**
     * Converts the order into a SQL expression for the ORDER BY clause.
     *
     * Resets and rebuilds {@see binds()} for this compilation.
     *
     * @return string A SQL string fragment.
     */
    public function sql()
    {
        $this->binds = [];

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
     * Custom conditions are trusted raw SQL for code-defined clauses only.
     * Never put request or user input in `condition`.
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

        $values = $this->normalizedValues($this->values());
        if (empty($values)) {
            throw new UnexpectedValueException(sprintf(
                'Value can not be empty on fields: %s',
                implode(', ', $fields)
            ));
        }

        $dir = $this->direction();
        $dir = $dir === null ? '' : ' ' . $dir;

        $placeholders = [];
        foreach ($values as $value) {
            $placeholders[] = $this->bindValue($value);
        }
        $valueList = implode(', ', $placeholders);

        $clauses = [];
        foreach ($fields as $fieldName) {
            $clauses[] = sprintf('FIELD(%1$s, %2$s)', $fieldName, $valueList) . $dir;
        }

        return implode(', ', $clauses);
    }

    /**
     * Normalize FIELD() list members to scalars (booleans cast to int).
     *
     * @param  mixed $values The value to be normalized.
     * @return array Returns a collection of scalar values ready to bind.
     */
    public function prepareValues($values)
    {
        return $this->normalizedValues($values);
    }

    /**
     * @param  mixed $values Raw order values.
     * @return array
     */
    protected function normalizedValues($values)
    {
        if ($values === null || $values === '' || $values === []) {
            return [];
        }

        if (!is_array($values)) {
            $values = (array)$values;
        }

        $normalized = [];
        foreach ($values as $value) {
            if (!is_scalar($value)) {
                continue;
            }

            $value = AbstractExpression::parseValue($value);

            if (is_bool($value)) {
                $normalized[] = (int)$value;
                continue;
            }

            $normalized[] = $value;
        }

        return $normalized;
    }

    /**
     * Register a bind value and return its SQL placeholder including the colon.
     *
     * @param  mixed $value The value to bind.
     * @return string
     */
    protected function bindValue($value)
    {
        $name = 'order_' . (self::$bindSequence++);
        $this->binds[$name] = $value;
        return ':' . $name;
    }
}
