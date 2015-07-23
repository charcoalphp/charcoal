<?php

namespace Charcoal\Source;

interface FilterInterface
{
    /**
    * @param array $data
    * @return Filter Chainable
    */
    public function set_data(array $data);

    /**
    * @param boolean $active
    * @return Filter (Chainable)
    */
    public function set_active($active);

    /**
    * @return boolean
    */
    public function active();

    /**
    * @param string $property
    * @throws InvalidArgumentException if the property argument is not a string
    * @return Filter (Chainable)
    */
    public function set_property($property);

    /**
    * @return string
    */
    public function property();
    /**
    * @param mixed $val
    * @return Filter (Chainable)
    */
    public function set_val($val);

    /**
    * @return mixed
    */
    public function val();

    /**
    * @param string $operator
    * @throws InvalidArgumentException if the parameter is not a valid operator
    * @return Filter (Chainable)
    */
    public function set_operator($operator);

    /**
    * @return string
    */
    public function operator();

    /**
    * @param string $func
    * @throws InvalidArgumentException if the parameter is not a valid function
    * @return Filter (Chainable)
    */
    public function set_func($func);

    /**
    * @return string
    */
    public function func();

    /**
    * @param string $operand
    * @throws InvalidArgumentException if the parameter is not a valid operand
    * @return Filter (Chainable)
    */
    public function set_operand($operand);
    /**
    * @return string
    */
    public function operand();

    /**
    * @param string $sql
    * @throws InvalidArgumentException if the parameter is not a valid operand
    * @return Filter (Chainable)
    */
    public function set_string($sql);

    /**
    * @return string
    */
    public function string();
}
