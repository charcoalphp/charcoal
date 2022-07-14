<?php

namespace Charcoal\Source;

use Countable;
use InvalidArgumentException;
// From 'charcoal-core'
use Charcoal\Source\Expression;
use Charcoal\Source\ExpressionInterface;
use Charcoal\Source\ExpressionFieldTrait;
use Charcoal\Source\FilterCollectionInterface;
use Charcoal\Source\FilterCollectionTrait;
use Charcoal\Source\FilterInterface;

/**
 * Filter Expression
 *
 * For affecting the results of a query that meet specified criteria.
 */
class Filter extends Expression implements
    Countable,
    FilterCollectionInterface,
    FilterInterface
{
    use ExpressionFieldTrait;
    use FilterCollectionTrait;

    public const DEFAULT_OPERATOR    = '=';
    public const DEFAULT_CONJUNCTION = 'AND';

    /**
     * The value on the right side of the operation.
     *
     * @var mixed
     */
    protected $value;

    /**
     * The operator used for comparing field and value.
     *
     * @var string
     */
    protected $operator = self::DEFAULT_OPERATOR;

    /**
     * The function name to be called on the field.
     *
     * @var string|null
     */
    protected $func;

    /**
     * The separator used for joining the internal expressions (i.e., conditions, filters).
     *
     * @var string
     */
    protected $conjunction = self::DEFAULT_CONJUNCTION;

    /**
     * Count the filters stored in this expression.
     *
     * @see    Countable
     * @return integer
     */
    public function count()
    {
        return count($this->filters);
    }

    /**
     * Set the filter clause data.
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
                    'Filter expression option "string" is deprecated in favour of "condition": %s',
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
                    'Filter expression option "table_name" is deprecated in favour of "table": %s',
                    $data['table_name']
                ),
                E_USER_DEPRECATED
            );
            $this->setTable($data['table_name']);
        }

        /** @deprecated */
        if (isset($data['val'])) {
            trigger_error(
                sprintf(
                    'Filter expression option "val" is deprecated in favour of "value": %s',
                    $data['val']
                ),
                E_USER_DEPRECATED
            );
            $this->setValue($data['val']);
        }

        /** @deprecated */
        if (isset($data['operand'])) {
            trigger_error(
                sprintf(
                    'Query expression option "operand" is deprecated in favour of "conjunction": %s',
                    $data['operand']
                ),
                E_USER_DEPRECATED
            );
            $this->setConjunction($data['operand']);
        }

        if (isset($data['table'])) {
            $this->setTable($data['table']);
        }

        if (isset($data['property'])) {
            $this->setProperty($data['property']);
        }

        if (isset($data['value'])) {
            $this->setValue($data['value']);
        }

        if (isset($data['values'])) {
            $this->setValue($data['values']);
        }

        if (isset($data['function'])) {
            $this->setFunc($data['function']);
        }

        if (isset($data['func'])) {
            $this->setFunc($data['func']);
        }

        if (isset($data['operator'])) {
            $this->setOperator($data['operator']);
        }

        if (isset($data['conjunction'])) {
            $this->setConjunction($data['conjunction']);
        }

        if (isset($data['conditions'])) {
            $this->addFilters($data['conditions']);
        }

        if (isset($data['filters'])) {
            $this->addFilters($data['filters']);
        }

        return $this;
    }

    /**
     * Retrieve the default values for filtering.
     *
     * @return array<string,mixed> An associative array.
     */
    public function defaultData()
    {
        return [
            'property'    => null,
            'table'       => null,
            'value'       => null,
            'func'        => null,
            'operator'    => self::DEFAULT_OPERATOR,
            'conjunction' => self::DEFAULT_CONJUNCTION,
            'filters'     => [],
            'condition'   => null,
            'active'      => true,
            'name'        => null,
        ];
    }

    /**
     * Retrieve the filter clause structure.
     *
     * @return array<string,mixed> An associative array.
     */
    public function data()
    {
        return [
            'property'    => $this->property(),
            'table'       => $this->table(),
            'value'       => $this->value(),
            'func'        => $this->func(),
            'operator'    => $this->operator(),
            'conjunction' => $this->conjunction(),
            'filters'     => $this->filters(),
            'condition'   => $this->condition(),
            'active'      => $this->active(),
            'name'        => $this->name(),
        ];
    }

    /**
     * Set the value used for comparison.
     *
     * @param  mixed $value The value on the right side of the comparison.
     * @return self
     */
    public function setValue($value)
    {
        $this->value = $this::parseValue($value);
        return $this;
    }

    /**
     * Retrieve the value used for comparison.
     *
     * @return mixed
     */
    public function value()
    {
        return $this->value;
    }

    /**
     * Set the operator used for comparing field and value.
     *
     * @param  string $operator The comparison operator.
     * @throws InvalidArgumentException If the parameter is not a valid operator.
     * @return self
     */
    public function setOperator($operator)
    {
        if (!is_string($operator)) {
            throw new InvalidArgumentException(
                'Operator should be a string.'
            );
        }

        $operator = strtoupper($operator);
        if (!in_array($operator, $this->validOperators())) {
            throw new InvalidArgumentException(sprintf(
                'Comparison operator "%s" not allowed in this context.',
                $operator
            ));
        }

        $this->operator = $operator;
        return $this;
    }

    /**
     * Retrieve the operator used for comparing field and value.
     *
     * @return string
     */
    public function operator()
    {
        return strtoupper($this->operator);
    }

    /**
     * Set the function to be called on the expression.
     *
     * @param  string $func The function name to invoke on the field.
     * @throws InvalidArgumentException If the parameter is not a valid function.
     * @return self
     */
    public function setFunc($func)
    {
        if ($func === null) {
            $this->func = $func;
            return $this;
        }

        if (!is_string($func)) {
            throw new InvalidArgumentException(
                'Function name should be a string.'
            );
        }

        $func = strtoupper($func);
        if (!in_array($func, $this->validFunc())) {
            throw new InvalidArgumentException(sprintf(
                'Function "%s" not allowed in this context.',
                $func
            ));
        }

        $this->func = $func;
        return $this;
    }

    /**
     * Retrieve the function to be called on the expression.
     *
     * @return string|null
     */
    public function func()
    {
        return $this->func;
    }

    /**
     * Set the conjunction for joining the conditions of this expression.
     *
     * @param  string $conjunction The separator to use.
     * @throws InvalidArgumentException If the parameter is not a valid conjunction.
     * @return self
     */
    public function setConjunction($conjunction)
    {
        if (!is_string($conjunction)) {
            throw new InvalidArgumentException(
                'Conjunction should be a string.'
            );
        }

        $conjunction = strtoupper($conjunction);
        if (!in_array($conjunction, $this->validConjunctions())) {
            throw new InvalidArgumentException(sprintf(
                'Conjunction "%s" not allowed in this context.',
                $conjunction
            ));
        }

        $this->conjunction = $conjunction;
        return $this;
    }

    /**
     * Retrieve the conjunction for the conditions of this expression.
     *
     * @return string
     */
    public function conjunction()
    {
        return $this->conjunction;
    }

    /**
     * Retrieve the supported comparison operators (in uppercase).
     *
     * @return string[]
     */
    protected function validOperators()
    {
        return [
            '!', 'NOT',
            '=', 'IS', '!=', 'IS NOT',
            'LIKE', 'NOT LIKE',
            'FIND_IN_SET',
            '>', '>=', '<', '<=',
            'IS NULL', 'IS NOT NULL',
            'IS TRUE', 'IS FALSE', 'IS UNKNOWN',
            'IS NOT TRUE', 'IS NOT FALSE', 'IS NOT UNKNOWN',
            '%', 'MOD',
            'IN', 'NOT IN',
            'REGEXP', 'NOT REGEXP'
        ];
    }

    /**
     * Retrieve the supported functions (in uppercase).
     *
     * @return string[]
     */
    protected function validFunc()
    {
        return [
            'ABS',
            'ACOS', 'ASIN', 'ATAN',
            'COS', 'COT', 'SIN', 'TAN',
            'CEIL', 'CEILING', 'FLOOR', 'ROUND', 'COUNT',
            'CHAR_LENGTH', 'CHARACTER_LENGTH', 'LENGTH', 'OCTET_LENGTH',
            'CRC32', 'MD5', 'SHA1',
            'DATE',
            'DAY', 'DAYNAME', 'DAYOFMONTH', 'DAYOFWEEK', 'DAYOFYEAR', 'LAST_DAY',
            'MONTH', 'MONTHNAME',
            'WEEK', 'WEEKDAY', 'WEEKOFYEAR', 'YEARWEEK',
            'YEAR',
            'QUARTER',
            'FROM_UNIXTIME',
            'HOUR', 'MICROSECOND', 'MINUTE', 'SECOND', 'TIME',
            'TIMESTAMP', 'UNIX_TIMESTAMP',
            'DEGREES', 'RADIANS',
            'EXP', 'LOG', 'LOG10', 'LN',
            'HEX',
            'LCASE', 'LOWER', 'UCASE', 'UPPER',
            'LTRIM', 'RTRIM', 'TRIM',
            'REVERSE',
            'SIGN',
            'SQRT'
        ];
    }

    /**
     * Retrieve the supported condition separators.
     *
     * @return string[] List of separators (case sensitive).
     */
    protected function validConjunctions()
    {
        return [
            'AND', '&&',
            'OR', '||',
            'XOR'
        ];
    }

    /**
     * Create a new filter expression.
     *
     * @see    FilterCollectionTrait::createFilter()
     * @param  array $data Optional expression data.
     * @return self
     */
    protected function createFilter(array $data = null)
    {
        $filter = new static();
        if ($data !== null) {
            $filter->setData($data);
        }
        return $filter;
    }

    /**
     * Alias of {@see self::traverseFilters()}
     *
     * @param  callable $callable The function to run for each expression.
     * @return self
     */
    public function traverse(callable $callable)
    {
        return $this->traverseFilters($callable);
    }

    /**
     * Clone this expression and its subtree of expressions.
     *
     * @return void
     */
    public function __clone()
    {
        foreach ($this->filters as $i => $filter) {
            if ($filter instanceof ExpressionInterface) {
                $this->filters[$i] = clone $filter;
            }
        }
    }
}
