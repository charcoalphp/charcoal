<?php

namespace Charcoal\Property;

use \PDO;

// From 'charcoal-property'
use \Charcoal\Property\StructureProperty;

/**
 * Map Structure Property
 */
class MapStructureProperty extends StructureProperty
{
    /**
     * Retrieve the property's type identifier.
     *
     * @return string
     */
    public function type()
    {
        return 'map-structure';
    }

    /**
     * Retrieve the property's SQL data type (storage format).
     *
     * For a lack of better array support in mysql, data is stored as encoded JSON in a LONGTEXT.
     *
     * @return string
     */
    public function sqlType()
    {
        return 'TEXT';
    }
}
