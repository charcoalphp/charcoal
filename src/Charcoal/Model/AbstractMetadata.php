<?php

namespace Charcoal\Model;

// Module `charcoal-config` dependencies
use \Charcoal\Config\AbstractConfig;

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
     * Holds the properties of this configuration object
     * @var array $properties
     */
    protected $properties = [];

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
}
