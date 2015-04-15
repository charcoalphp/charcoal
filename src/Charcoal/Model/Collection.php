<?php

namespace Charcoal\Model;

use \Charcoal\Collection\AbstractCollection as AbstractCollection;
use \Charcoal\Model\IndexableInterface as IndexableInterface;

/**
* Model Collection
*/
class Collection extends AbstractCollection
{
    /**
    * Array of (ordered) objects
    * @access private
    */
    private $_objects;

    /**
    * Identity Map
    * Ensures that each object gets loaded only once by keeping every loaded object in a map. Looks up objects using the map when referring to them.
    * @access private
    */
    private $_map;

    /**
    * ArrayAccess > offsetSet
    *
    * Note that collection does not support setting object to a specific key (The object's ID is always used for this).
     *
    * @param mixed $offset
    * @param mixed $value
    *
    * @throws \InvalidArgumentException if the value is not a Object or offset is set
    */
    public function offsetSet($offset, $value)
    {
        if(!($value instanceof IndexableInterface)) {
            throw new \InvalidArgumentException('Collection value must be an Object');
        }
        if($offset === null) {
            $this->_objects[] = $value;
            $this->_map[$value->id()] = $value;
        }
        else {
            throw new \InvalidArgumentException('Collection can set to an offset. Use [].');
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
        if(is_int($offset)) {
            return isset($this->_objects[$offset]);
        }
        else if(is_string($offset)) {
            return isset($this->_map[$offset]);
        }
    }

    /**
     * ArrayAccess > offsetUnset
     *
     * @param mixed $offset
     *
     * @throws \InvalidArgumentException if the offset is not an integer or string
     */
    public function offsetUnset($offset)
    {
        if(is_int($offset)) {
            $id = $this->_objects[$offset]->id();
            unset($this->_objects[$offset]);
            unset($this->_map[$id]);

        }
        else if(is_string($offset)) {
            $pos = $this->pos($offset);
            unset($this->_map[$offset]);
            unset($this->_objects[$pos]);
        }
        else {
            throw new \InvalidArgumentException('Offset should be either an integer or a string');
        }
    }

    /**
    * ArrayAccess > offsetGet
    *
    * @param mixed $offset
    *
    * @throws \InvalidArgumentException if the offset is not an integer or string
    */
    public function offsetGet($offset)
    {
        if(is_int($offset)) {
            return isset($this->_objects[$offset]) ? $this->_objects[$offset] : null;
        }
        else if(is_string($offset)) {
            return isset($this->_map[$offset]) ? $this->_map[$offset] : null;
        }
        else {
            throw new \InvalidArgumentException('Offset should be either an integer or a string');
        }
    }

    /**
    * IteratorAggregate > getIterator
    */
    public function getIterator()
    {
        if(empty($this->_map)) {
            // Empty object
            return new \ArrayIterator();
        }
        return new \ArrayIterator($this->_map);
    }


    /**
    * Countable > count
    *
    * By implementing the Countable interface, the php count() function can be called directly on a list.
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
    public function add(Object $obj)
    {
        $this->_objects[] = $obj;
        $this->_map[$obj->id()] = $obj;

        // Chainable
        return $this;
    }

    /**
    * @param string|IndexableInterface
    *
    * @throws \InvalidArgumentException if the offset is not a string
    * @return int|false
    */
    public function pos($key)
    {

        if(is_string($key)) {
            return array_search($key, array_keys($this->_map));
        }
        else if($key instanceof IndexableInterface) {
            return array_search($key->id(), array_keys($this->_map));
        }
        else {
            throw new \InvalidArgumentException('Key must be a string or an IndexableInterface');
        }
        
    }
}
