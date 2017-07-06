<?php

namespace Charcoal\Property;

// From 'charcoal-property'
use Charcoal\Property\StructureProperty;

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
}
