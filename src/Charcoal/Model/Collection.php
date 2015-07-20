<?php

namespace Charcoal\Model;

use \InvalidArgumentException as InvalidArgumentException;

use \Charcoal\Collection\AbstractCollection as AbstractCollection;
use \Charcoal\Core\IndexableInterface as IndexableInterface;

/**
* Model Collection
*/
class Collection extends AbstractCollection
{
    /**
    * Array of (ordered) objects
    * @var array $_objects
    */
    private $_objects;

    /**
    * Identity Map
    *
    * Ensures that each object gets loaded only once by keeping
    * every loaded object in a map. Looks up objects using the
    * map when referring to them.
    * @var array $_map
    */
    private $_map;

    /**
    * ArrayAccess > offsetSet
    *
    * Note that collection does not support setting object to a specific key
    * (The object's ID is always used for this).
    *
    * @param mixed $offset
    * @param mixed $value
    * @throws InvalidArgumentException if the value is not a Object or offset is set
    * @return void
    */
    public function offsetSet($offset, $value)
    {
        if (!($value instanceof IndexableInterface)) {
            throw new InvalidArgumentException('Collection value must be a IndexableInterface object.');
        }
        if ($offset === null) {
            $this->_objects[] = $value;
            $this->_map[$value->id()] = $value;
        } else {
            throw new InvalidArgumentException('Collection value can be set like an array.');
        }
    }

    /**
    * ArrayAccess > offsetExists
    *
    * @param  mixed $offset
    * @return boolean
    */
    public function offsetExists($offset)
    {
        if (is_int($offset)) {
            return isset($this->_objects[$offset]);
        } elseif (is_string($offset)) {
            return isset($this->_map[$offset]);
        }
    }

    /**
     * ArrayAccess > offsetUnset
     *
     * @param mixed $offset
     * @throws InvalidArgumentException if the offset is not an integer or string
     * @return void
     */
    public function offsetUnset($offset)
    {
        if (is_int($offset)) {
            $id = $this->_objects[$offset]->id();
            unset($this->_objects[$offset]);
            unset($this->_map[$id]);

        } elseif (is_string($offset)) {
            $pos = $this->pos($offset);
            unset($this->_map[$offset]);
            unset($this->_objects[$pos]);
        } else {
            throw new InvalidArgumentException('Offset should be either an integer or a string.');
        }
    }

    /**
    * ArrayAccess > offsetGet
    *
    * @param mixed $offset
    * @throws InvalidArgumentException if the offset is not an integer or string
    * @return void
    */
    public function offsetGet($offset)
    {
        if (is_int($offset)) {
            return (isset($this->_objects[$offset]) ? $this->_objects[$offset] : null);
        } elseif (is_string($offset)) {
            return (isset($this->_map[$offset]) ? $this->_map[$offset] : null);
        } else {
            throw new InvalidArgumentException('Offset should be either an integer or a string.');
        }
    }

    /**
    * IteratorAggregate > getIterator
    *
    * @return mixed
    */
    public function getIterator()
    {
        if (empty($this->_map)) {
            // Empty object
            return new \ArrayIterator();
        }
        return new \ArrayIterator($this->_map);
    }

    /**
    * Countable > count
    *
    * By implementing the Countable interface, the PHP `count()` function
    * can be called directly on a list.
    *
    * @return integer The number of objects in the list
    */
    public function count()
    {
        return count($this->_objects);
    }

    /**
    * Get the ordered object array
    *
    * @return array
    */
    public function objects()
    {
        return $this->_objects;
    }

    /**
    * Get the map array, with IDs as keys
    *
    * @return array
    */
    public function map()
    {
        return $this->_map;
    }

    /**
    * Manually add an object to the list
    *
    * @param Charcoal_Base $obj
    *
    * @return \Charcoal\Collection (Chainable)
    */
    public function add(IndexableInterface $obj)
    {
        $this->_objects[] = $obj;
        $this->_map[$obj->id()] = $obj;

        // Chainable
        return $this;
    }

    /**
    * @param string|IndexableInterface $key
    * @throws InvalidArgumentException if the offset is not a string
    * @return integer|boolean
    */
    public function pos($key)
    {
        if (is_string($key)) {
            return array_search($key, array_keys($this->_map));
        } elseif ($key instanceof IndexableInterface) {
            return array_search($key->id(), array_keys($this->_map));
        } else {
            throw new InvalidArgumentException('Key must be a string or an IndexableInterface object.');
        }
    }
}
