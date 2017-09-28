<?php

namespace Charcoal\Source;

// From 'charcoal-core'
use Charcoal\Source\FieldInterface;

/**
 * Defines a Condition Expression.
 */
interface FilterInterface extends FieldInterface
{
    /**
     * Set the value used for comparison.
     *
     * @param  mixed $val The value on the right side of the comparison.
     * @return FilterInterface Chainable
     */
    public function setVal($val);

    /**
     * Retrieve the value used for comparison.
     *
     * @return mixed
     */
    public function val();

    /**
     * Set the operator used for comparing field and value.
     *
     * @param  string $operator The comparison operator.
     * @throws InvalidArgumentException If the parameter is not a valid operator.
     * @return FilterInterface Chainable
     */
    public function setOperator($operator);

    /**
     * Retrieve the operator used for comparing field and value.
     *
     * @return string
     */
    public function operator();

    /**
     * Set the function to be called on the expression.
     *
     * @param  string $func The function name to invoke on the field.
     * @throws InvalidArgumentException If the parameter is not a valid function.
     * @return FilterInterface Chainable
     */
    public function setFunc($func);

    /**
     * Retrieve the function to be called on the expression.
     *
     * @return string
     */
    public function func();

    /**
     * Set the operator used for joining the next filter.
     *
     * @param  string $operand The logical operator.
     * @return FilterInterface Chainable
     */
    public function setOperand($operand);

    /**
     * Retrieve the operator used for joining the next filter.
     *
     * @return string
     */
    public function operand();
}
