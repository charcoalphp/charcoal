<?php

declare(strict_types=1);

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
     */
    #[\Override]
    public function type(): string
    {
        return 'map-structure';
    }
}
