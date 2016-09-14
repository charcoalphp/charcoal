<?php

namespace Charcoal\Model;

// Module `charcoal-config` dependencies
use \Charcoal\Config\AbstractConfig;

// Module `charcoal-property` dependencies
use \Charcoal\Property\PropertyInterface;

// Local namespace dependencies
use \Charcoal\Model\MetadataInterface;

/**
 * An implementation, as abstract class, of `MetadataInterface`.
 *
 * This class also implements the `ArrayAccess`, so properties can be accessed with `[]`.
 * The `LoadableInterface` is also implemented, mostly through `LoadableTrait`.
 */
abstract class AbstractMetadata extends AbstractConfig implements
    MetadataInterface
{
    /**
     * @var array $defaultData
     */
    protected $defaultData = [];

    /**
     * Holds the properties of this configuration object
     * @var array $properties
     */
    protected $properties = [];

    /**
     * @var PropertyInterface[]
     */
    protected $propertiesObjects;

    /**
     * @param array $defaultData
     * @return MetadataInterface Chainable
     */
    public function setDefaultData(array $defaultData)
    {
        $this->defaultData = $defaultData;
        return $this;
    }

    /**
     * @return array
     */
    public function defaultData()
    {
        return $this->defaultData;
    }

    /**
     * Set the properties.
     *
     * @param array $properties The properties.
     * @throws InvalidArgumentException If parameter is not an array.
     * @return MetadataInterface Chainable
     */
    public function setProperties(array $properties)
    {
        $this->properties = $properties;
        return $this;
    }

    /**
     * Retrieve the properties.
     *
     * @return array
     */
    public function properties()
    {
        return $this->properties;
    }

    /**
     * Retrieve the given property.
     *
     * @param string $propertyIdent The property identifier.
     * @return array|null
     */
    public function property($propertyIdent)
    {
        if (isset($this->properties[$propertyIdent])) {
            return $this->properties[$propertyIdent];
        } else {
            return null;
        }
    }

    /**
     * Set a property, as a PropertyInterface instance.
     *
     * @param string            $propertyIdent  The property indentifer.
     * @param PropertyInterface $propertyObject The property, as object.
     * @return MetadataInterface Chainable
     */
    public function setPropertyObject($propertyIdent, PropertyInterface $propertyObject)
    {
        $this->propertiesObjects[$propertyIdent] = $propertyObject;
        return $this;
    }

    /**
     * @param string The property (identifier) to return, as an object.
     * @return PropertyInterface|null
     */
    public function propertyObject($propertyIdent)
    {
        if (!isset($this->propertiesObjects[$propertyIdent])) {
            return null;
        } else {
            return $this->propertiesObjects[$propertyIdent];
        }
    }
}
