<?php

namespace Charcoal\Model;

// Dependencies from `PHP`
use \ArrayAccess;
use \ArrayIterator;
use \InvalidArgumentException;
use \IteratorAggregate;
use \JsonSerializable;
use \Serializable;
use \Traversable;

// Dependencies from `container-interop`
use \Interop\Container\ContainerInterface;

/**
*
*/
abstract class AbstractEntity implements
    ArrayAccess,
    ContainerInterface,
    IteratorAggregate,
    JsonSerializable,
    Serializable
{
    /**
     * Keep a list of all config keys available.
     * @var array $keys
     */
    private $keys = [];

    /**
     * Get the entity available keys.
     *
     * @return array
     */
    public function keys()
    {
        return array_keys($this->keys);
    }

    /**
    * Gets the entity data, as associative array map.
    *
    * @param
    * @return array The data map.
    */
    public function data(array $filters = [])
    {
        unset($filters); // unused
        $ret = [];
        $keys = $this->keys();
        foreach ($keys as $k) {
            $ret[$k] = $this[$k];
        }
        return $ret;
    }

    /**
    * Sets the entity data, from associative array map (or any other Traversable).
    *
    * This function takes an array and fill the property with its value.
    *
    * This method either calls a setter for each key (`set_{$key}()`) or sets a public member.
    *
    * For example, calling with `merge(['ident'=>$ident])` would call `set_ident($ident)`
    * becasue `set_ident()` exists.
    *
    * But calling with `merge(['foobar'=>$foo])` would set the `$foobar` member
    * on the metadata object, because the method `set_foobar()` does not exist.
    *
    * @param array|Traversable $data
    * @return AbstractProperty Chainable
    */
    public function merge($data)
    {
        foreach ($data as $prop => $val) {
            $this[$prop] = $val;
        }

        return $this;
    }

    /**
    * @param string $key Identifier of the entry to set.
    * @param mixed $val The value to set.
    * @return AbstractEntity Chainable
    */
    public function set($key, $val)
    {
        $this[$key] = $val;
        return $this;
    }

    /**
     * ContainerInterface > get()
     *
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     * @throws NotFoundException  No entry was found for this identifier.
     * @throws ContainerException Error while retrieving the entry.
     * @return mixed Entry.
     * @see ContainerInterface::get()
     */
    public function get($key)
    {
        return $this[$key];
    }

    /**
     * ContainerInterface > get()
     *
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($key)` returning true does not mean that `get($key)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundException`.
     *
     * @param string $key Identifier of the entry to look for.
     *
     * @return boolean
     */
    public function has($key)
    {
        return isset($this[$key]);
    }

    /**
     * Satisfies the Traversable / IteratorAggrefate interface.
     *
     * @return Traversable
     */
    public function getIterator()
    {
        return new ArrayIterator($this->data());
    }

    /**
     * JsonSerializable > jsonSerialize()
     *
     *
     */
    public function jsonSerialize()
    {
        return $this->data();
    }

    /**
     * Serializable > serialize()
     *
     * @return string
     */
    public function serialize()
    {
        return serialize($this->data());
    }

    /**
     * Serializable > unserialize()
     *
     * @param string $serialized The serialized data (with `serialize()`).
     */
    public function unserialize($serialized)
    {
        $unserialized = unserialize($serialized);
        $this->merge($unserialized);
    }

    /**
     * ArrayAccess > offsetExists()
     *
     * @param string $key
     * @throws InvalidArgumentException If the key argument is not a string or is a "numeric" value.
     * @return boolean
     */
    public function offsetExists($key)
    {
        if (is_numeric($key)) {
            throw new InvalidArgumentException(
                'Config array access only supports non-numeric keys.'
            );
        }
        $getter = $key;
        if (is_callable([$this, $getter])) {
            $value = $this->{$getter}();
        } else {
            if (!isset($this->{$key})) {
                return false;
            }
            $value = $this->{$key};
        }
        return ($value !== null);
    }

    /**
     * ArrayAccess > offsetGet()
     *
     * @param string $key
     * @throws InvalidArgumentException If the key argument is not a string or is a "numeric" value.
     * @return mixed The value (or null)
     */
    public function offsetGet($key)
    {
        if (is_numeric($key)) {
            throw new InvalidArgumentException(
                'Config array access only supports non-numeric keys.'
            );
        }
        $getter = $key;
        if (is_callable([$this, $getter])) {
            return $this->{$getter}();
        } else {
            if (isset($this->{$key})) {
                return $this->{$key};
            } else {
                return null;
            }
        }
    }

    /**
     * ArrayAccess > offsetSet()
     *
     * @param string $key
     * @param mixed  $value
     * @throws InvalidArgumentException If the key argument is not a string or is a "numeric" value.
     * @return void
     */
    public function offsetSet($key, $value)
    {
        if (is_numeric($key)) {
            throw new InvalidArgumentException(
                'Config array access only supports non-numeric keys.'
            );
        }
        $setter = $this->setter($key);
        if (is_callable([$this, $setter])) {
            $this->{$setter}($value);
        } else {
            $this->{$key} = $value;
        }
        $this->keys[$key] = true;
    }

    /**
     * ArrayAccess > offsetUnset()
     *
     * @param string $key
     * @throws InvalidArgumentException If the key argument is not a string or is a "numeric" value.
     * @return void
     */
    public function offsetUnset($key)
    {
        if (is_numeric($key)) {
            throw new InvalidArgumentException(
                'Entity array access only supports non-numeric keys.'
            );
        }
        $this[$key] = null;
        unset($this->keys[$key]);
    }

    /**
     * Allow an object to define are the setter are usually
     *
     * @return string
     */
    private function setter($key)
    {
        return 'set_'.$key;
    }
}
