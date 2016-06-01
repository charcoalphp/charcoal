<?php

namespace Charcoal\Source;

interface OrderInterface
{
    /**
     * @param array|\ArrayAccess $data The order data.
     * @return Order Chainable
     */
    public function setData($data);

    /**
     * @param string $property The property to order with.
     * @return Order (Chainable)
     */
    public function setProperty($property);

    /**
     * @return string
     */
    public function property();

    /**
     * @param string $mode The order mode.
     * @return Order Chainable
     */
    public function setMode($mode);

    /**
     * @return string
     */
    public function mode();

    /**
     * Set the values.
     * Values are ignored if the mode is not "values"
     *
     * If the `$values` argument is a string, it will be split by ",".
     * If it is an array, the values will be used as is.
     * Otherwise, the function will throw an error
     *
     * @param  string|array $values The order values.
     * @return Order (Chainable)
     */
    public function setValues($values);

    /**
     * @return array
     */
    public function values();
}
