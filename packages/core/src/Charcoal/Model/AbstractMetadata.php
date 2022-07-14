<?php

namespace Charcoal\Model;

// From 'charcoal-config'
use Charcoal\Config\AbstractConfig;
// From 'charcoal-property'
use Charcoal\Property\PropertyInterface;
// From 'charcoal-core'
use Charcoal\Model\MetadataInterface;

/**
 * A basic metadata container.
 *
 * Abstract implementation of {@see \Charcoal\Model\MetadataInterface}.
 *
 * This class also implements the `ArrayAccess`, so properties can be accessed with `[]`.
 * The `LoadableInterface` is also implemented, mostly through `LoadableTrait`.
 */
abstract class AbstractMetadata extends AbstractConfig implements
    MetadataInterface
{
    /**
     * Holds the default values of this configuration object.
     *
     * @var array
     */
    protected $defaultData = [];

    /**
     * Holds the properties of this configuration object.
     *
     * @var array
     */
    protected $properties = [];

    /**
     * Stores the properties, as objects, of this configuration object.
     *
     * @var PropertyInterface[]
     */
    protected $propertiesObjects;

    /**
     * Set the object's default values.
     *
     * @param array $defaultData An associative array.
     * @return self
     */
    public function setDefaultData(array $defaultData)
    {
        $this->defaultData = $defaultData;
        return $this;
    }

    /**
     * Retrieve the default values.
     *
     * @return array
     */
    public function defaultData()
    {
        return $this->defaultData;
    }

    /**
     * Set the properties. Ensure the property idents (keys) are camel-cased.
     *
     * @param array $properties One or more properties.
     * @return self
     */
    public function setProperties(array $properties)
    {
        $this->properties = [];
        foreach ($properties as $k => $v) {
            $this->properties[$this->camelize($k)] = $v;
        }
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
    public function property($propertyIdent = null)
    {
        $propertyIdent = $this->camelize($propertyIdent);
        if (isset($this->properties[$propertyIdent])) {
            return $this->properties[$propertyIdent];
        } else {
            return null;
        }
    }

    /**
     * Assign an instance of {@see PropertyInterface} to the given property.
     *
     * @param string            $propertyIdent  The property indentifer.
     * @param PropertyInterface $propertyObject The property, as an object.
     * @return self
     */
    public function setPropertyObject($propertyIdent, PropertyInterface $propertyObject)
    {
        $this->propertiesObjects[$propertyIdent] = $propertyObject;
        return $this;
    }

    /**
     * Retrieve the given property as an object.
     *
     * @param string $propertyIdent The property (identifier) to return, as an object.
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
