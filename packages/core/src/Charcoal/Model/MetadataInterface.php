<?php

namespace Charcoal\Model;

// From 'charcoal-property'
use Charcoal\Property\PropertyInterface;
// From 'charcoal-config'
use Charcoal\Config\ConfigInterface;

/**
 * Defines a metadata container.
 *
 * Metadata is typically used to describe an object.
 */
interface MetadataInterface extends ConfigInterface
{
    /**
     * Set the object's default values.
     *
     * @param array $defaultData An associative array.
     * @return self
     */
    public function setDefaultData(array $defaultData);

    /**
     * Retrieve the default values.
     *
     * @return array
     */
    public function defaultData();

    /**
     * Retrieve the properties.
     *
     * @return array
     */
    public function properties();

    /**
     * Retrieve the given property.
     *
     * @param string $propertyIdent The property identifier.
     * @return array|null
     */
    public function property($propertyIdent);

    /**
     * Assign an instance of {@see PropertyInterface} to the given property.
     *
     * @param string            $propertyIdent  The property indentifer.
     * @param PropertyInterface $propertyObject The property, as object.
     * @return self
     */
    public function setPropertyObject($propertyIdent, PropertyInterface $propertyObject);

    /**
     * Retrieve the given property as an object.
     *
     * @param string $propertyIdent The property (identifier) to return, as an object.
     * @return PropertyInterface|null
     */
    public function propertyObject($propertyIdent);
}
