<?php

namespace Charcoal\Source\Database;

use UnexpectedValueException;

// From 'charcoal-core'
use Charcoal\Source\Database\DatabaseExpressionInterface;
use Charcoal\Source\DatabaseSource;
use Charcoal\Source\Filter;

/**
 * The DatabaseFilter makes a Filter SQL-aware.
 */
class DatabaseFilter extends Filter implements
    DatabaseExpressionInterface
{
    /**
     * The table related to the field identifier.
     *
     * @var string $tableName
     */
    protected $tableName = DatabaseSource::DEFAULT_TABLE_ALIAS;

    /**
     * Retrieve the default values for filtering.
     *
     * @return array
     */
    public function defaultData()
    {
        $defaults = parent::defaultData();
        $defaults['table_name'] = DatabaseSource::DEFAULT_TABLE_ALIAS;

        return $defaults;
    }

    /**
     * Retrieve the Filter's SQL as a string to append to a WHERE clause.
     *
     * @return string
     */
    public function sql()
    {
        if ($this->active() === false) {
            return '';
        }

        if ($this->hasString()) {
            return $this->byCondition();
        }

        if ($this->hasFields()) {
            return $this->byPredicate();
        }

        return '';
    }

    /**
     * Retrieve the custom WHERE condition.
     *
     * @throws UnexpectedValueException If the custom condition is empty.
     * @return string
     */
    protected function byCondition()
    {
        if (!$this->hasString()) {
            throw new UnexpectedValueException(
                'Custom expression can not be empty.'
            );
        }

        return $this->string();
    }

    /**
     * Retrieve the WHERE condition.
     *
     * @todo   Values are often not quoted.
     * @throws UnexpectedValueException If any required property, function, or operator is empty.
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
        $value      = $this->val();
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
                    if (is_array($value)) {
                        $value = implode(',', $value);
                    }

                    $conditions[] = sprintf('%1$s(\'%2$s\', %3$s)', $operator, $value, $target);
                    break;

                case 'IS NULL':
                case 'IS NOT NULL':
                    $conditions[] = sprintf('(%1$s %2$s)', $target, $operator);
                    break;

                case 'IN':
                case 'NOT IN':
                    if (is_array($value)) {
                        $value = implode('\',\'', $value);
                    }

                    $conditions[] = sprintf('(%1$s %2$s (\'%3$s\'))', $target, $operator, $value);
                    break;

                default:
                    $conditions[] = sprintf('(%1$s %2$s \'%3$s\')', $target, $operator, $value);
                    break;
            }
        }

        if (count($conditions) > 1) {
            /**
             * @todo This would be a good occasion to implement "operand"
             */
            $conditions = '('.implode(' OR ', $conditions).')';
        } else {
            $conditions = implode('', $conditions);
        }

        return $conditions;
    }
}
