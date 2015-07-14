<?php

namespace Charcoal\Config;

use \ArrayAccess as ArrayAccess;
use \InvalidArgumentException as InvalidArgumentException;

use \Charcoal\Config\ConfigInterface as ConfigInterface;

/**
* An abstract class that fulfills the full ConfigInterface.
*
* This class also implements the `ArrayAccess` interface, so each member can be accessed with `[]`.
*/
abstract class AbstractConfig implements
    ConfigInterface,
    ArrayAccess
{

    /**
    * @param array|string|null $data Optional default data, as `[$key => $val]` array
    * @throws InvalidArgumentException if data is not an array
    */
    public function __construct($data = null)
    {
        if (is_string($data)) {
            $this->add_file($data);
        } else if (is_array($data)) {
             $data = array_merge($this->default_data(), $data);
             $this->set_data($data);
        } else if ($data === null) {
            $data = $this->default_data();
            $this->set_data($data);
        } else {
            throw new InvalidArgumentException('Data must be an array.');
        }
    }

    /**
    * @param array $data
    * @return AbstractConfig Chainable
    */
    abstract public function set_data(array $data);

    /**
    * ConfigInterface > default_data
    * @return array
    */
    public function default_data()
    {
        return [];
    }

    /**
    * ArrayAccess > offsetExists()
    *
    * @param string $offset
    * @throws InvalidArgumentException if $offset is not a string / numeric
    * @return boolean
    */
    public function offsetExists($offset)
    {
        if (is_numeric($offset)) {
            throw new InvalidArgumentException('Config array access only supports non-numeric keys.');
        }
        return isset($this->{$offset});
    }

    /**
    * ArrayAccess > offsetGet()
    *
    * @param string $offset
    * @throws InvalidArgumentException if $offset is not a string / numeric
    * @return mixed The value (or null)
    */
    public function offsetGet($offset)
    {
        if (is_numeric($offset)) {
            throw new InvalidArgumentException('Config array access only supports non-numeric keys.');
        }
        return (isset($this->{$offset}) ? $this->{$offset} : null);
    }

    /**
    * ArrayAccess > offsetSet()
    *
    * @param string $offset
    * @param mixed  $value
    * @throws InvalidArgumentException if $offset is not a string / numeric
    * @return void
    */
    public function offsetSet($offset, $value)
    {
        if (is_numeric($offset)) {
            throw new InvalidArgumentException('Config array access only supports non-numeric keys.');
        }
        $this->{$offset} = $value;
    }

    /**
    * ArrayAccess > offsetUnset()
    *
    * @param string $offset
    * @throws InvalidArgumentException if $offset is not a string / numeric
    * @return void
    */
    public function offsetUnset($offset)
    {
        if (is_numeric($offset)) {
            throw new InvalidArgumentException('Config array access only supports non-numeric keys.');
        }
        $this->{$offset} = null;
        unset($this->{$offset});
    }

    /**
    * @param string $filename
    * @throws InvalidArgumentException if the filename is not a string or not valid json / php
    * @return AbstractConfig (Chainable)
    * @todo Load with Flysystem
    */
    public function add_file($filename)
    {
        if (!is_string($filename)) {
            throw new InvalidArgumentException('Config File must be a string.');
        }

        if (pathinfo($filename, PATHINFO_EXTENSION) == 'php') {
            include $filename;
        } else if (pathinfo($filename, PATHINFO_EXTENSION) == 'json') {
            if (file_exists($filename)) {
                $file_content = file_get_contents($filename);
                $config = json_decode($file_content, true);
                $this->set_data($config);
            }
        } else {
            throw new InvalidArgumentException('Only JSON and PHP files are accepted as a Config File.');
        }

        return $this;
    }
}
