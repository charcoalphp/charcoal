<?php

namespace Charcoal\Source;

use InvalidArgumentException;

// From 'charcoal-core'
use Charcoal\Source\AbstractExpression;
use Charcoal\Source\FieldTrait;
use Charcoal\Source\FilterInterface;

/**
 * Filter Expression
 */
class Filter extends AbstractExpression implements
    FilterInterface
{
    use FieldTrait;

    const DEFAULT_OPERATOR = '=';
    const DEFAULT_OPERAND  = 'AND';

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
     * The operator used for joining the next filter.
     *
     * @var string
     */
    protected $operand = self::DEFAULT_OPERAND;

    /**
     * Set the filter clause data.
     *
     * @param  array $data The clause data.
     * @return Filter Chainable
     */
    public function setData(array $data)
    {
        parent::setData($data);

        /** @deprecated */
        if (isset($data['table_name'])) {
            trigger_error(
                sprintf(
                    'Query Expression option "table_name" is deprecated in favour of "table": %s',
                    $data['table_name']
                ),
                E_USER_DEPRECATED
            );
            $this->setTable($data['table_name']);
        }

        if (isset($data['val'])) {
            trigger_error(
                sprintf(
                    'Query expression option "val" is deprecated in favour of "value": %s',
                    $data['val']
                ),
                E_USER_DEPRECATED
            );
            $this->setValue($data['val']);
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

        if (isset($data['func'])) {
            $this->setFunc($data['func']);
        }

        if (isset($data['operator'])) {
            $this->setOperator($data['operator']);
        }

        if (isset($data['operand'])) {
            $this->setOperand($data['operand']);
        }

        return $this;
    }

    /**
     * Retrieve the default values for filtering.
     *
     * @return array<string,mixed>
     */
    public function defaultData()
    {
        return [
            'property'  => null,
            'table'     => null,
            'value'     => null,
            'func'      => null,
            'operator'  => self::DEFAULT_OPERATOR,
            'operand'   => self::DEFAULT_OPERAND,
            'condition' => null,
            'active'    => true,
            'name'      => null,
        ];
    }

    /**
     * Retrieve the filter clause structure.
     *
     * @return array<string,mixed>
     */
    public function data()
    {
        $data = [
            'property'  => $this->property(),
            'table'     => $this->table(),
            'value'     => $this->value(),
            'func'      => $this->func(),
            'operator'  => $this->operator(),
            'operand'   => $this->operand(),
            'condition' => $this->condition(),
            'active'    => $this->active(),
            'name'      => $this->name(),
        ];

        return array_udiff_assoc($data, $this->defaultData(), [ $this, 'diffValues' ]);
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
     * @return Filter Chainable
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
            throw new InvalidArgumentException(
                'This is not a valid operator.'
            );
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
     * @return Filter Chainable
     */
    public function setFunc($func)
    {
        if ($func === null) {
            $this->func = $func;
            return $this;
        }

        if (!is_string($func)) {
            throw new InvalidArgumentException(
                'Func should be a string.'
            );
        }

        $func = strtoupper($func);
        if (!in_array($func, $this->validFunc())) {
            throw new InvalidArgumentException(
                'This is not a valid function.'
            );
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
     * Set the operator used for joining the next filter.
     *
     * @param  string $operand The logical operator.
     * @throws InvalidArgumentException If the parameter is not a valid operand.
     * @return Filter Chainable
     */
    public function setOperand($operand)
    {
        if (!is_string($operand)) {
            throw new InvalidArgumentException(
                'Operand should be a string.'
            );
        }

        $operand = strtoupper($operand);
        if (!in_array($operand, $this->validOperands())) {
            throw new InvalidArgumentException(
                'This is not a valid operand.'
            );
        }

        $this->operand = $operand;

        return $this;
    }

    /**
     * Retrieve the operator used for joining the next filter.
     *
     * @return string
     */
    public function operand()
    {
        return strtoupper($this->operand);
    }

    /**
     * Retrieve the supported comparison operators (in uppercase).
     *
     * @return array
     */
    protected function validOperators()
    {
        return [
            '=', 'IS', '!=', 'IS NOT',
            'LIKE', 'NOT LIKE',
            'FIND_IN_SET',
            '>', '>=', '<', '<=',
            'IS NULL', 'IS NOT NULL',
            '%', 'MOD',
            'IN','NOT IN',
            'REGEXP', 'NOT REGEXP'
        ];
    }

    /**
     * Retrieve the supported logical operators (in uppercase).
     *
     * @return array
     */
    protected function validOperands()
    {
        return [
            'AND', '&&',
            'OR', '||',
            'XOR'
        ];
    }

    /**
     * Retrieve the supported functions (in uppercase).
     *
     * @return array
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
}
