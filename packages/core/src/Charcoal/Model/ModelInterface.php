<?php

namespace Charcoal\Model;

// From 'charcoal-config'
use Charcoal\Config\EntityInterface;
use Charcoal\Source\StorableInterface;

/**
 * Describes a data model.
 */
interface ModelInterface extends EntityInterface, DescribableInterface, StorableInterface
{
    /**
     * Set the model data (from a flattened structure).
     *
     * This method is useful for processing a 1-dimensional array.
     *
     * Example:
     * ```
     * {
     *     "title_en": "Charcoal",
     *     "title_fr": "Charbon"
     * }
     * ```
     *
     * @param  array $data The model data.
     * @return ModelInterface Returns the current model.
     */
    public function setFlatData(array $data);

    /**
     * Retrieve the model data as a flattened structure.
     *
     * @return array
     */
    public function flatData();

    /**
     * Retrieve the default values.
     *
     * @return array
     */
    public function defaultData();

    /**
     * Retrieve the model's properties.
     *
     * @param  array $propertyIdents Optional. List of property identifiers
     *     for retrieving a subset of property objects.
     * @return \Charcoal\Property\PropertyInterface[]
     */
    public function properties(array $propertyIdents = null);

    /**
     * Retrieve an instance of {@see PropertyInterface} for the given property.
     *
     * @param  string $propertyIdent The property (ident) to get.
     * @throws \InvalidArgumentException If the property identifier is invalid.
     * @return \Charcoal\Property\PropertyInterface
     */
    public function property($propertyIdent);

    /**
     * Alias of {@see ModelInterface::property()} and {@see ModelInterface::properties()}.
     *
     * @param  string|null $propertyIdent Optional property identifier.
     * @return \Charcoal\Property\PropertyInterface|\Charcoal\Property\PropertyInterface[] If $propertyIdent is NULL,
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
