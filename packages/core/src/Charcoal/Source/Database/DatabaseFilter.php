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
     * Converts the filter into a SQL expression for the WHERE clause.
     *
     * @return string A SQL string fragment.
     */
    public function sql()
    {
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
     * Retrieve the custom WHERE condition.
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
            if ($filter instanceof DatabaseExpressionInterface) {
                $filter = $filter->sql();
            }

            if ($filter && strlen($filter) > 0) {
                $conditions[] = $filter;
            }
        }

        return $this->compileConditions($conditions);
    }

    /**
     * Retrieve the WHERE condition.
     *
     * @todo   Values are often not quoted.
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

                    if (is_array($value)) {
                        $value = implode(',', $value);
                    }

                    $conditions[] = sprintf('%2$s(\'%3$s\', %1$s)', $target, $operator, $value);
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

                    if (is_array($value)) {
                        $value = implode('\',\'', $value);
                    }

                    $conditions[] = sprintf('%1$s %2$s (\'%3$s\')', $target, $operator, $value);
                    break;

                default:
                    if ($value === null) {
                        throw new UnexpectedValueException(sprintf(
                            'Value is required on field "%s" for "%s"',
                            $target,
                            $operator
                        ));
                    }

                    $conditions[] = sprintf('%1$s %2$s \'%3$s\'', $target, $operator, $value);
                    break;
            }
        }

        return $this->compileConditions($conditions, 'OR');
    }
}
