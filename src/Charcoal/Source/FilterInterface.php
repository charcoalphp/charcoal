<?php

namespace Charcoal\Source;

interface FilterInterface
{
    /**
    * @param array $data
    * @return Filter Chainable
    */
    public function setData(array $data);

    /**
    * @param boolean $active
    * @return Filter (Chainable)
    */
    public function setActive($active);

    /**
    * @return boolean
    */
    public function active();

    /**
    * @param string $property
    * @throws InvalidArgumentException if the property argument is not a string
    * @return Filter (Chainable)
    */
    public function setProperty($property);

    /**
    * @return string
    */
    public function property();
    /**
    * @param mixed $val
    * @return Filter (Chainable)
    */
    public function setVal($val);

    /**
    * @return mixed
    */
    public function val();

    /**
    * @param string $operator
    * @throws InvalidArgumentException if the parameter is not a valid operator
    * @return Filter (Chainable)
    */
    public function setOperator($operator);

    /**
    * @return string
    */
    public function operator();

    /**
    * @param string $func
    * @throws InvalidArgumentException if the parameter is not a valid function
    * @return Filter (Chainable)
    */
    public function setFunc($func);

    /**
    * @return string
    */
    public function func();

    /**
    * @param string $operand
    * @throws InvalidArgumentException if the parameter is not a valid operand
    * @return Filter (Chainable)
    */
    public function setOperand($operand);
    /**
    * @return string
    */
    public function operand();

    /**
    * @param string $sql
    * @throws InvalidArgumentException if the parameter is not a valid operand
    * @return Filter (Chainable)
    */
    public function setString($sql);

    /**
    * @return string
    */
    public function string();
}
