<?php

namespace Charcoal\Property;

use PDO;

// From 'charcoal-property'
use Charcoal\Property\AbstractProperty;

/**
 * Number Property
 */
class NumberProperty extends AbstractProperty
{
    /**
     * Minimal value.
     *
     * If null (default), then skip minimum validation (no constraint).
     *
     * @var mixed|null
     */
    private $min;

    /**
     * Maximal value.
     *
     * If null (default), then skip maximum validation (no constrant).
     *
     * @var mixed|null
     */
    private $max;

    /**
     * @return string
     */
    public function type()
    {
        return 'number';
    }

    /**
     * Set the minimal value.
     *
     * @param mixed|null $min The minimal value.
     * @return self
     */
    public function setMin($min)
    {
        $this->min = $min;
        return $this;
    }

    /**
     * Retrieves the minimal value.
     *
     * @return mixed|null
     */
    public function min()
    {
        return $this->min;
    }

    /**
     * Set the maximal value.
     *
     * @param mixed|null $max The maximal value.
     * @return self
     */
    public function setMax($max)
    {
        $this->max = $max;
        return $this;
    }

    /**
     * Retrieves the maximal value.
     *
     * @return mixed|null
     */
    public function max()
    {
        return $this->max;
    }


    /**
     * The property's default validation methods.
     *
     * @return string[]
     */
    public function validationMethods()
    {
        $parentMethods = parent::validationMethods();

        return array_merge($parentMethods, [
            'max',
            'min'
        ]);
    }

    /**
     * Get the SQL type (Storage format)
     *
     * Stored as `VARCHAR` for max_length under 255 and `TEXT` for other, longer strings
     *
     * @see StorablePropertyTrait::sqlType()
     * @return string The SQL type
     */
    public function sqlType()
    {
        // Multiple number are stocked as TEXT because we do not know the maximum length
        if ($this['multiple']) {
            return 'TEXT';
        }

        return 'DOUBLE';
    }

    /**
     * @see StorablePropertyTrait::sqlPdoType()
     * @return integer
     */
    public function sqlPdoType()
    {
        return PDO::PARAM_STR;
    }
}
