<?php

namespace Charcoal\Source;

// From 'charcoal-core'
use Charcoal\Source\ExpressionFieldInterface;

/**
 * Describes a sorting expression.
 */
interface OrderInterface extends
    ExpressionFieldInterface
{
    /**
     * Set the, pre-defined, sorting mode.
     *
     * @param  string|null $mode The sorting mode.
     * @throws InvalidArgumentException If the mode is not a string or invalid.
     * @return OrderInterface Returns the current expression.
     */
    public function setMode($mode);

    /**
     * Retrieve the sorting mode.
     *
     * @return string|null
     */
    public function mode();

    /**
     * Set the sorting direction.
     *
     * @param  string|null $direction The direction to sort on.
     * @throws InvalidArgumentException If the direction is not a string.
     * @return OrderInterface Returns the current expression.
     */
    public function setDirection($direction);

    /**
     * Retrieve the sorting direction.
     *
     * @return string|null
     */
    public function direction();

    /**
     * Set the values to sort against.
     *
     * @param  string|array $values A list of field values.
     * @throws InvalidArgumentException If the parameter is not an array or a string.
     * @return OrderInterface Returns the current expression.
     */
    public function setValues($values);

    /**
     * Determine if the Order expression has values.
     *
     * @return boolean
     */
    public function hasValues();

    /**
     * Retrieve the values to sort against.
     *
     * @return array|null A list of field values or NULL if no values were assigned.
     */
    public function values();
}
