<?php

namespace Charcoal\Source;

interface FilterInterface
{
    /**
     * @param array $data The filter data.
     * @return Filter Chainable
     */
    public function setData(array $data);

    /**
     * @param boolean $active The active flag.
     * @return FilterInterface Chainable
     */
    public function setActive($active);

    /**
     * @return boolean
     */
    public function active();

    /**
     * @param string $property The filter's property.
     * @throws InvalidArgumentException If the property argument is not a string.
     * @return FilterInterface Chainable
     */
    public function setProperty($property);

    /**
     * @return string
     */
    public function property();
    /**
     * @param mixed $val The filter value.
     * @return FilterInterface Chainable
     */
    public function setVal($val);

    /**
     * @return mixed
     */
    public function val();

    /**
     * @param string $operator The filter operator.
     * @return FilterInterface Chainable
     */
    public function setOperator($operator);

    /**
     * @return string
     */
    public function operator();

    /**
     * @param string $func The filter function.
     * @return FilterInterface Chainable
     */
    public function setFunc($func);

    /**
     * @return string
     */
    public function func();

    /**
     * @param string $operand The filter operand.
     * @return FilterInterface Chainable
     */
    public function setOperand($operand);
    /**
     * @return string
     */
    public function operand();

    /**
     * @param string $tableName The table name (default to objTable).
     * @return FilterInterface Chainable
     */
    public function setTableName($tableName);
    /**
     * @return string
     */
    public function tableName();

    /**
     * @param string $sql The filter SQL string.
     * @return FilterInterface Chainable
     */
    public function setString($sql);

    /**
     * @return string
     */
    public function string();
}
