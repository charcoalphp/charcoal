<?php

namespace Charcoal\Metadata;

use \ArrayAccess as ArrayAccess;
use \Charcoal\Metadata\MetadataInterface as MetadataInterface;
use \Charcoal\Metadata\MetadataLoader as MetadataLoader;

use \Charcoal\Loader\LoadableInterface as LoadableInterface;
use \Charcoal\Loader\LoadableTrait as LoadableTrait;

/**
* An implementation, as abstract class, of `MetadataInterface`.
*
* This class also implements the `ArrayAccess`, so properties can be accessed with `[]`.
* The `LoadableInterface` is also implemented, mostly through `LoadableTrait`.
*/
abstract class AbstractMetadata implements
    MetadataInterface,
    LoadableInterface,
    ArrayAccess
{
    use LoadableTrait;

    /**
     * Holds the properties of this configuration object
     * @var array $properties
     */
    protected $_properties = [];

    /**
    * @param array $data
    * @throws \InvalidArgumentException if the data parameter is not an array
    * @return Metadata (Chainable)
    */
    public function set_data($data)
    {
        if(!is_array($data)) {
            throw new \InvalidArgumentException('Data parameter must be an array');
        }

        if(isset($data['properties'])) {
            $this->set_properties($data['properties']);
        }

        foreach($data as $k => $v) {
            $this->{$k} = $v;
        }

        return $this;
    }

    /**
    * @param array $properties
    * @throws \InvalidArgumentException if parameter is not an array
    * @return MetadataInterface Chainable
    */
    public function set_properties($properties)
    {
        if(!is_array($properties)) {
            throw new \InvalidArgumentException('Properties need to be an array');
        }
        $this->_properties = $properties;
        return $this;
    }

    /**
    * @return array
    */
    public function properties()
    {
        return $this->_properties;
    }

    /**
     * ArrayAccess isset(config[a])
     */
    public function offsetExists($offset)
    {
        return isset($this->{$offset});
    }

    /**
     * ArrayAccess config[a]
     */
    public function offsetGet($offset)
    {
        return isset($this->{$offset}) ? $this->{$offset} : null;
    }

    /**
    * ArrayAccess config[a] = '';
    * @throws \InvalidArgumentException if the offset is not set ($config[] = '')
    */
    public function offsetSet($offset, $value)
    {
        if(empty($offset)) {
            throw new \InvalidArgumentException('Offset is required');
        }
        $this->{$offset} = $value;
    }

    /**
     *  ArrayAcces unset(config[a])
     */
    public function offsetUnset($offset)
    {
        $this->{$offset} = null;
        unset($this->{$offset});
    }

    /**
    * @return LoaderInterface
    */
    public function create_loader()
    {
        $loader = new MetadataLoader();
        return $loader;
    }

}
