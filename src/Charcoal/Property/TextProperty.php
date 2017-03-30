<?php

namespace Charcoal\Property;

// From 'charcoal-property'
use Charcoal\Property\StringProperty;

/**
 * Text Property. Longer strings.
 */
class TextProperty extends StringProperty
{
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
        return 'TEXT';
    }
}
