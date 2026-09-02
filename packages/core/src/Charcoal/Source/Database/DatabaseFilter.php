<?php

namespace Charcoal\Source\Database;

use UnexpectedValueException;
// From 'charcoal-core'
use Charcoal\Source\Database\DatabaseExpressionInterface;
use Charcoal\Source\DatabaseSource;
use Charcoal\Source\Filter;

/**
 * SQL Filter Expression
 *
 * Priority of SQL resolution if the expression is "active":
 * 1. Nested — If "filters" is not empty.
 *    - Optionally, a logical NOT "operator" can negate the tree by prepending NOT.
 * 2. Custom — If "condition" is defined.
 *    - Optionally, a logical NOT "operator" can negate the tree by prepending NOT.
 * 3. Predicate — If "property" and either "func" or "value" are defined.
 *
 * Predicate values are emitted as named PDO placeholders; use {@see binds()} with the SQL.
 */
class DatabaseFilter extends Filter implements
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
     * Process-wide counter so nested filters never collide on placeholder names.
     *
     * @var integer
     */
    private static $bindSequence = 0;

    /**
     * Retrieve the default values for filtering.
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
     * Merge binds from a nested filter into this expression.
     *
     * @param  array<string,mixed> $binds Parameter map (keys without leading colon).
     * @return self
     */
    public function mergeBinds(array $binds)
    {
        $this->binds = array_merge($this->binds, $binds);
        return $this;
    }

    /**
     * Converts the filter into a SQL expression for the WHERE clause.
     *
     * Resets and rebuilds {@see binds()} for this compilation.
     *
     * @return string A SQL string fragment.
     */
    public function sql()
    {
        $this->binds = [];

        if ($this->active()) {
            if ($this->hasFilters()) {
                $sql = $this->byFilters();
                if ($this->isNegating()) {
                    return $this->operator() . ' ' . $sql;
                }
                return $sql;
            }

            if ($this->hasCondition()) {
                $sql = $this->byCondition();
                if ($this->isNegating()) {
                    return $this->operator() . ' (' . $sql . ')';
                }
                return $sql;
            }

            if ($this->hasFields()) {
                return $this->byPredicate();
            }
        }

        return '';
    }

    /**
     * Determine if the expression negates the final result.
     *
     * @return boolean TRUE if the expression uses a logical NOT operator, FALSE otherwise.
     */
    public function isNegating()
    {
        return in_array($this->operator(), [ '!', 'NOT' ]);
    }

    /**
     * Compile the list of conditions.
     *
     * @param  string[]    $conditions  The list of conditions to compile.
     * @param  string|null $conjunction The condition separator.
     * @return string
     */
    protected function compileConditions(array $conditions, $conjunction = null)
    {
        if (count($conditions) === 1) {
            return $conditions[0];
        }

        if ($conjunction === null) {
            $conjunction = $this->conjunction();
        }

        return '(' . implode(' ' . $conjunction . ' ', $conditions) . ')';
    }

    /**
     * Allocate a unique bind parameter name (without leading colon).
     *
     * @return string
     */
    protected function nextBindName()
    {
        return 'filter_' . (self::$bindSequence++);
    }

    /**
     * Register a bind value and return its SQL placeholder including the colon.
     *
     * @param  mixed $value The value to bind.
     * @return string
     */
    protected function bindValue($value)
    {
        $name = $this->nextBindName();
        $this->binds[$name] = $value;
        return ':' . $name;
    }

    /**
     * Retrieve the custom WHERE condition.
     *
     * Custom conditions are trusted raw SQL for code-defined clauses only
     * (e.g. `NOW()`, column-to-column comparisons). They do not contribute binds.
     * Never put request or user input in `condition`; use property/operator/value
     * predicates so values are parameterized.
     *
     * @throws UnexpectedValueException If the custom condition is empty.
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
     * Retrieve the correctly parenthesized and nested WHERE conditions.
     *
     * @throws UnexpectedValueException If the custom condition is empty.
     * @return string
     */
    protected function byFilters()
    {
        if (!$this->hasFilters()) {
            throw new UnexpectedValueException(
                'Filters can not be empty.'
            );
        }

        $conditions = [];
        foreach ($this->filters() as $filter) {
            if ($filter instanceof DatabaseFilter) {
                $sql = $filter->sql();
                $this->mergeBinds($filter->binds());
            } elseif ($filter instanceof DatabaseExpressionInterface) {
                $sql = $filter->sql();
            } else {
                $sql = $filter;
            }

            if ($sql && strlen($sql) > 0) {
                $conditions[] = $sql;
            }
        }

        return $this->compileConditions($conditions);
    }

    /**
     * Retrieve the WHERE condition.
     *
     * Predicate values are bound via named PDO placeholders.
     *
     * @throws UnexpectedValueException If any required property, function, operator, or value is empty.
     * @return string
     */
    protected function byPredicate()
    {
        $fields = $this->fieldIdentifiers();
        if (empty($fields)) {
            throw new UnexpectedValueException(
                'Property is required.'
            );
        }

        $conditions = [];
        $value      = $this->value();
        $operator   = $this->operator();
        $function   = $this->func();
        foreach ($fields as $fieldName) {
            if ($function !== null) {
                $target = sprintf('%1$s(%2$s)', $function, $fieldName);
            } else {
                $target = $fieldName;
            }

            switch ($operator) {
                case 'FIND_IN_SET':
                    if ($value === null) {
                        throw new UnexpectedValueException(sprintf(
                            'Value is required on field "%s" for "%s"',
                            $target,
                            $operator
                        ));
                    }

                    $needle = is_array($value) ? implode(',', $value) : $value;
                    $placeholder = $this->bindValue($needle);
                    $conditions[] = sprintf('%2$s(%3$s, %1$s)', $target, $operator, $placeholder);
                    break;

                case '!':
                case 'NOT':
                    $conditions[] = sprintf('%2$s %1$s', $target, $operator);
                    break;

                case 'IS NULL':
                case 'IS TRUE':
                case 'IS FALSE':
                case 'IS UNKNOWN':
                case 'IS NOT NULL':
                case 'IS NOT TRUE':
                case 'IS NOT FALSE':
                case 'IS NOT UNKNOWN':
                    $conditions[] = sprintf('%1$s %2$s', $target, $operator);
                    break;

                case 'IN':
                case 'NOT IN':
                    if ($value === null) {
                        throw new UnexpectedValueException(sprintf(
                            'Value is required on field "%s" for "%s"',
                            $target,
                            $operator
                        ));
                    }

                    $items = is_array($value) ? array_values($value) : [ $value ];
                    if (count($items) === 0) {
                        // Empty IN is never true; avoid invalid SQL.
                        $conditions[] = ($operator === 'NOT IN') ? '1=1' : '0=1';
                        break;
                    }

                    $placeholders = [];
                    foreach ($items as $item) {
                        $placeholders[] = $this->bindValue($item);
                    }
                    $conditions[] = sprintf(
                        '%1$s %2$s (%3$s)',
                        $target,
                        $operator,
                        implode(', ', $placeholders)
                    );
                    break;

                default:
                    if ($value === null) {
                        throw new UnexpectedValueException(sprintf(
                            'Value is required on field "%s" for "%s"',
                            $target,
                            $operator
                        ));
                    }

                    $placeholder = $this->bindValue($value);
                    $conditions[] = sprintf('%1$s %2$s %3$s', $target, $operator, $placeholder);
                    break;
            }
        }

        return $this->compileConditions($conditions, 'OR');
    }
}
