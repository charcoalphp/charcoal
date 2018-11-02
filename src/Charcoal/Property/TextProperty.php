<?php

namespace Charcoal\Property;

// From 'charcoal-property'
use Charcoal\Property\StringProperty;

/**
 * Text Property. Longer strings.
 */
class TextProperty extends StringProperty
{
    const DEFAULT_LONG = false;

    /**
     * @var boolean
     */
    private $long = self::DEFAULT_LONG;

    /**
     * @param boolean $long Whether long text are supported.
     * @return self
     */
    public function setLong($long)
    {
        $this->long = !!$long;
        return $this;
    }

    /**
     * @return boolean
     */
    public function long()
    {
        return $this->long;
    }

    /**
     * @return string
     */
    public function type()
    {
        return 'text';
    }

    /**
     * String's default max length is overridden for the text property.
     * (0 = no max length).
     *
     * @return integer
     */
    public function defaultMaxLength()
    {
        return 0;
    }

    /**
     * Get the SQL type (Storage format)
     *
     * @return string The SQL type
     */
    public function sqlType()
    {
        if ($this->long() === true) {
            return 'LONGTEXT';
        } else {
            return 'TEXT';
        }
    }
}
