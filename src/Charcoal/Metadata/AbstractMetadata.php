<?php

namespace Charcoal\Metadata;

// Dependencies from `PHP`
use \ArrayAccess as ArrayAccess;
use \InvalidArgumentException as InvalidArgumentException;

// Intra-module (`charcoal-core`) dependencies
use \Charcoal\Loader\LoadableInterface as LoadableInterface;
use \Charcoal\Loader\LoadableTrait as LoadableTrait;

// Local namespace dependencies
use \Charcoal\Metadata\MetadataInterface as MetadataInterface;
use \Charcoal\Metadata\MetadataLoader as MetadataLoader;

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
     * @var array $_properties
     */
    protected $_properties = [];

    /**
    * @param array $data
    * @return AbstractMetadata Chainable
    */
    public function set_data(array $data)
    {
        if (isset($data['properties'])) {
            $this->set_properties($data['properties']);
            unset($data['properties']);
        }

        foreach ($data as $k => $v) {
            $this->{$k} = $v;
        }

        return $this;
    }

    /**
    * @param array $properties
    * @throws InvalidArgumentException if parameter is not an array
    * @return MetadataInterface Chainable
    */
    public function set_properties(array $properties)
    {
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
    *
    * @param mixed $offset
    * @return boolean
    */
    public function offsetExists($offset)
    {
        return isset($this->{$offset});
    }

    /**
    * ArrayAccess config[a]
    *
    * @param mixed $offset
    * @return mixed
    */
    public function offsetGet($offset)
    {
        return (isset($this->{$offset}) ? $this->{$offset} : null);
    }

    /**
    * ArrayAccess > offsetSet $config[a] = '';
    *
    * @param string $offset
    * @param mixed  $value
    * @throws InvalidArgumentException if the offset is not set ($config[] = '')
    * @return void
    */
    public function offsetSet($offset, $value)
    {
        if (empty($offset)) {
            throw new InvalidArgumentException('Offset is required.');
        }
        $this->{$offset} = $value;
    }

    /**
    * ArrayAcces > offsetUnest `unset($config[a])`
    *
    * @param mixed $offset
    * @return void
    */
    public function offsetUnset($offset)
    {
        $this->{$offset} = null;
        unset($this->{$offset});
    }

    /**
    * @param array $data Optional
    * @return LoaderInterface
    */
    protected function create_loader(array $data = null)
    {
        $loader = new MetadataLoader();
        if (is_array($data)) {
            $loader->set_data($data);
        }
        return $loader;
    }
}
