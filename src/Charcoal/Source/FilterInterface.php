<?php

namespace Charcoal\Source;

// From 'charcoal-core'
use Charcoal\Source\ExpressionFieldInterface;

/**
 * Describes a predicate expression.
 */
interface FilterInterface extends
    ExpressionFieldInterface
{
    /**
     * Set the value used for comparison.
     *
     * @param  mixed $value The value on the right side of the comparison.
     * @return self
     */
    public function setValue($value);

    /**
     * Retrieve the value used for comparison.
     *
     * @return mixed
     */
    public function value();

    /**
     * Set the operator used for comparing field and value.
     *
     * @param  string $operator The comparison operator.
     * @throws \InvalidArgumentException If the parameter is not a valid operator.
     * @return self
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
     * @throws \InvalidArgumentException If the parameter is not a valid function.
     * @return self
     */
    public function setFunc($func);

    /**
     * Retrieve the function to be called on the expression.
     *
     * @return string
     */
    public function func();
}
