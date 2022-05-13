<?php

namespace Charcoal\Property;

use PDO;

// From 'charcoal-property'
use Charcoal\Property\AbstractProperty;

/**
 * The most basic (generic) property possible, from abstract.
 */
class GenericProperty extends AbstractProperty
{
    /**
     * @return string
     */
    public function type()
    {
        return 'generic';
    }

    /**
     * @see StorablePropertyTrait::sqlType()
     * @return string
     */
    public function sqlType()
    {
        if ($this['multiple']) {
            return 'TEXT';
        } else {
            return 'VARCHAR(255)';
        }
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
