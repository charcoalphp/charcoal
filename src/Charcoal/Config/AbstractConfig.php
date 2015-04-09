<?php

namespace Charcoal\Config;

use \ArrayAccess as ArrayAccess;

use \Charcoal\Config\ConfigInterface as ConfigInterface;

/**
* An abstract class that fulfills the full ConfigInterface
*
* This class also implements the `ArrayAccess` interface, so each member can be accessed with `[]`.
*/
abstract class AbstractConfig implements
    ConfigInterface,
    ArrayAccess
{
    
    /**
    * @param array $data Optional default data, as `[$key=>$val]` array
    * @throws \InvalidArgumentException if data is not an array
    */
    final public function __construct($data=null)
    {
        if(is_array($data)) {
             $data = array_merge($this->default_data(), $data);
        }
        else if($data === null) {
            $data = $this->default_data();
        }
        else {
            throw new \InvalidArgumentException('Data must be an array');
        }

        $this->set_data($data);
    }

    abstract public function set_data($data);

    /**
    * ConfigInterface > default_data
    */
    public function default_data()
    {
        return [];
    }

    /**
    * ArrayAccess > offsetExists()
    *
    * @param string $offset
    * @throws \InvalidArgumentException if $offset is not a string / numeric
    * @return boolean
    */
    public function offsetExists($offset)
    {
        if(is_numeric($offset)) {
            throw new \InvalidArgumentException('Config array access only supports non-numeric keys');
        }
        return isset($this->{$offset});
    }

    /**
    * ArrayAccess > offsetGet()
    *
    * @param string $offset
    * @throws \InvalidArgumentException if $offset is not a string / numeric
    * @return mixed The value (or null)
    */
    public function offsetGet($offset)
    {
        if(is_numeric($offset)) {
            throw new \InvalidArgumentException('Config array access only supports non-numeric keys');
        }
        return isset($this->{$offset}) ? $this->{$offset} : null;
    }

    /**
    * ArrayAccess > offsetSet()
    *
    * @param string $offset
    * @param mixed $value
    * @throws \InvalidArgumentException if $offset is not a string / numeric
    */
    public function offsetSet($offset, $value)
    {
        if(is_numeric($offset)) {
            throw new \InvalidArgumentException('Config array access only supports non-numeric keys');
        }
        $this->{$offset} = $value;
    }

    /**
    * ArrayAccess > offsetUnset()
    *
    * @param string $offset
    * @throws \InvalidArgumentException if $offset is not a string / numeric
    */
    public function offsetUnset($offset)
    {
        if(is_numeric($offset)) {
            throw new \InvalidArgumentException('Config array access only supports non-numeric keys');
        }
        $this->{$offset} = null;
        unset($this->{$offset});
    }
}
