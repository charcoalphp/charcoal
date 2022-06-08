<?php

namespace Charcoal\Property;

// From 'charcoal-core'
use Charcoal\Model\DescribableInterface;

// From 'charcoal-property'
use Charcoal\Property\PropertyInterface;

/**
 * Defines a model having attributes that allow the formatting of its data.
 */
interface DescribablePropertyInterface extends DescribableInterface
{
    /**
     * Retrieve the model's properties.
     *
     * @param  array $propertyIdents Optional. List of property identifiers
     *     for retrieving a subset of property objects.
     * @return PropertyInterface[]
     */
    public function properties(array $propertyIdents = null);

    /**
     * Retrieve an instance of {@see PropertyInterface} for the given property.
     *
     * @param  string $propertyIdent The property (ident) to get.
     * @throws \InvalidArgumentException If the property identifier is invalid.
     * @return PropertyInterface
     */
    public function property($propertyIdent);

    /**
     * Alias of {@see ModelInterface::property()} and {@see ModelInterface::properties()}.
     *
     * @param  string|null $propertyIdent Optional property identifier.
     * @return PropertyInterface|PropertyInterface[] If $propertyIdent is NULL,
     *     returns all properties. Otherwise, returns the property associated with $propertyIdent.
     */
    public function p($propertyIdent = null);

    /**
     * Determine if the model has the given property.
     *
     * @param  string $propertyIdent The property identifier to lookup.
     * @throws \InvalidArgumentException If the property identifier is invalid.
     * @return boolean
     */
    public function hasProperty($propertyIdent);
}
