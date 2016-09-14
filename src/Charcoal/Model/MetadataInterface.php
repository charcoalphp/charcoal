<?php

namespace Charcoal\Model;

// Module `charcoal-property` dependencies
use \Charcoal\Property\PropertyInterface;

/**
 * Metadata is typically used to describe an object.
 *
 * Metadata
 */
interface MetadataInterface
{
    /**
     * @param array $defaultData
     * @return MetadataInterface Chainable
     */
    public function setDefaultData(array $defaultData);

    /**
     * @return array
     */
    public function defaultData();

    /**
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
     * Set a property, as a PropertyInterface instance.
     *
     * @param string            $propertyIdent  The property indentifer.
     * @param PropertyInterface $propertyObject The property, as object.
     * @return MetadataInterface Chainable
     */
    public function setPropertyObject($propertyIdent, PropertyInterface $propertyObject);

    /**
     * @param string The property (identifier) to return, as an object.
     * @return PropertyInterface|null
     */
    public function propertyObject($propertyIdent);
}
