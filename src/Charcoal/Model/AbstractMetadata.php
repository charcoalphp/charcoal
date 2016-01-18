<?php

namespace Charcoal\Model;

// Dependencies from `PHP`
use \ArrayAccess;
use \InvalidArgumentException;

// Module `charcoal-config` dependencies
use \Charcoal\Config\AbstractConfig;

// Intra-module (`charcoal-core`) dependencies
use \Charcoal\Loader\LoadableInterface;
use \Charcoal\Loader\LoadableTrait;

// Local namespace dependencies
use \Charcoal\Model\MetadataInterface;
use \Charcoal\Model\MetadataLoader;

/**
* An implementation, as abstract class, of `MetadataInterface`.
*
* This class also implements the `ArrayAccess`, so properties can be accessed with `[]`.
* The `LoadableInterface` is also implemented, mostly through `LoadableTrait`.
*/
abstract class AbstractMetadata extends AbstractConfig implements
    MetadataInterface,
    LoadableInterface,
    ArrayAccess
{
    use LoadableTrait;

    /**
     * Holds the properties of this configuration object
     * @var array $properties
     */
    protected $properties = [];

    /**
    * @param array $properties
    * @throws InvalidArgumentException if parameter is not an array
    * @return MetadataInterface Chainable
    */
    public function setProperties(array $properties)
    {
        $this->properties = $properties;
        return $this;
    }

    /**
    * @return array
    */
    public function properties()
    {
        return $this->properties;
    }

    /**
    * @param array $data Optional
    * @return LoaderInterface
    */
    protected function createLoader(array $data = null)
    {
        $loader = new MetadataLoader();
        if (is_array($data)) {
            $loader->setData($data);
        }
        return $loader;
    }
}
