<?php

namespace Charcoal\Source;

// From 'charcoal-core'
use Charcoal\Source\FieldInterface;

/**
 * Defines an Order Expression.
 */
interface OrderInterface extends FieldInterface
{
    /**
     * Set the, pre-defined, sorting mode.
     *
     * @param  string $mode The order mode.
     * @throws InvalidArgumentException If the mode is not a string or invalid.
     * @return OrderInterface Chainable
     */
    public function setMode($mode);

    /**
     * Retrieve the sorting mode.
     *
     * @return string The order mode.
     */
    public function mode();

    /**
     * Set the values to sort against.
     *
     * @param  string|array $values A list of field values.
     * @throws InvalidArgumentException If the parameter is not an array or a string.
     * @return OrderInterface Chainable
     */
    public function setValues($values);

    /**
     * Retrieve the values to sort against.
     *
     * @return array|null A list of field values or NULL if no values were assigned.
     */
    public function values();
}
