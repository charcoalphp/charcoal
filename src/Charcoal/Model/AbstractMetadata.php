<?php

namespace Charcoal\Model;

// Dependencies from `PHP`
use \ArrayAccess;
use \InvalidArgumentException;

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
    protected $properties = [];

    /**
    * Convert an array of parameters in the metadata format.
    *
    * This method either calls a setter for each key (`set_{$key}()`) or sets a public member.
    *
    * For example, calling with `set_data(['properties'=>$properties])` would call
    * `set_properties($properties)`, because `set_properties()` exists.
    *
    * But calling with `set_data(['foobar'=>$foo])` would set the `$foobar` member
    * on the metadata object, because the method `set_foobar()` does not exist.
    *
    * @param array $data
    * @return AbstractMetadata Chainable
    */
    public function set_data(array $data)
    {
        foreach ($data as $prop => $val) {
            $func = [$this, 'set_'.$prop];
            if (is_callable($func)) {
                call_user_func($func, $val);
                unset($data[$prop]);
            } else {
                $this->{$prop} = $val;
            }
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
            throw new InvalidArgumentException(
                'Offset is required.'
            );
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
