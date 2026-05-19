<?php

namespace Charcoal\Property;

use InvalidArgumentException;
// From 'charcoal-property'
use Charcoal\Property\ModelStructureProperty;
use Charcoal\Property\TemplateProperty;

/**
 * Template Options Property
 */
class TemplateOptionsProperty extends ModelStructureProperty
{
    /**
     * Retrieve the property's type identifier.
     */
    #[\Override]
    public function type(): string
    {
        return 'template-options';
    }

    /**
     * Add the given metadata interfaces for the property to use as a structure.
     *
     * @see    StructureProperty::addStructureInterface()
     * @param  string $interface A metadata interface to use.
     * @throws InvalidArgumentException If the template property value is invalid.
     */
    #[\Override]
    public function addStructureInterface($interface): static
    {
        if ($interface instanceof TemplateProperty) {
            $interface = (string)$interface;
            if ($interface === '0') {
                throw new InvalidArgumentException(
                    'Invalid template structure interface'
                );
            }
        }

        return parent::addStructureInterface($interface);
    }
}
