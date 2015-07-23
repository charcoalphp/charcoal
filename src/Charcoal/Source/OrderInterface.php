<?php

namespace Charcoal\Source;

interface OrderInterface
{
        /**
    * @param array $data
    * @return Order Chainable
    */
    public function set_data(array $data);
    /**
    * @param string $property
    * @throws InvalidArgumentException if the property argument is not a string
    * @return Order (Chainable)
    */
    public function set_property($property);

    /**
    * @return string
    */
    public function property();

    /**
    * @param string $mode
    * @throws InvalidArgumentException
    * @return Order Chainable
    */
    public function set_mode($mode);

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
    * @throws InvalidArgumentException if the parameter is not an array or a string
    * @param  string|array $values
    * @return Order (Chainable)
    */
    public function set_values($values);

    /**
    * @return array
    */
    public function values();
}
