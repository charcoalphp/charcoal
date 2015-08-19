<?php

namespace Charcoal\Config;

// Dependencies from `PHP`
use \ArrayAccess as ArrayAccess;
use \InvalidArgumentException as InvalidArgumentException;

// Local namespace dependencies
use \Charcoal\Config\ConfigInterface as ConfigInterface;

/**
* Configuration container / registry.
*
* An abstract class that fulfills the full ConfigInterface.
*
* This class also implements the `ArrayAccess` interface, so each member can be accessed with `[]`.
*/
abstract class AbstractConfig implements
    ConfigInterface,
    ArrayAccess
{
    const DEFAULT_SEPARATOR = '/';

    /**
    * @var string $_separator
    */
    protected $_separator = self::DEFAULT_SEPARATOR;

    /**
    * @param array|string|null $data Optional default data, as `[$key => $val]` array
    * @throws InvalidArgumentException if data is not an array
    */
    public function __construct($data = null)
    {
        if (is_string($data)) {
            $this->set_data($this->default_data());
            $this->add_file($data);
        } elseif (is_array($data)) {
             $data = array_merge($this->default_data(), $data);
             $this->set_data($data);
        } elseif ($data === null) {
            $data = $this->default_data();
            $this->set_data($data);
        } else {
            throw new InvalidArgumentException(
                'Data must be an array.'
            );
        }
    }

    /**
    * @param string $separator
    * @throws InvalidArgumentException
    * @return AbstractConfig Chainable
    */
    public function set_separator($separator)
    {
        if (!is_string($separator)) {
            throw new InvalidArgumentException(
                'Separator needs to be a string.'
            );
        }
        if (strlen($separator) > 1) {
            throw new InvalidArgumentException(
                'Separator needs to be only one-character.'
            );
        }
        $this->_separator = $separator;
        return $this;
    }

    /**
    * @return string
    */
    public function separator()
    {
        return $this->_separator;
    }

    /**
    * @param array $data
    * @return AbstractConfig Chainable
    */
    public function set_data(array $data)
    {
        foreach ($data as $k => $v) {
                $this->set($k, $v);
        }
        return $this;
    }

        /**
    * ConfigInterface > default_data
    *
    * A stub for when the default data is empty.
    * Mae sure to reimplement in children Config classes if any default data should be set.
    * @return array
    */
    public function default_data()
    {
        return [];
    }


    /**
    * @param string $key
    * @return mixed
    */
    public function get($key)
    {
        $arr = $this;
        $split_keys = explode($this->separator(), $key);
        foreach ($split_keys as $k) {
            if (!isset($arr[$k])) {
                return null;
            }
            if (!is_array($arr[$k]) && ($arr[$k] instanceof ArrayAccess)) {
                return $arr[$k];
            }
            $arr = $arr[$k];
        }
        return $arr;
    }

    /**
    * @param string $key
    * @return boolean
    */
    public function has($key)
    {
        $arr = $this;
        $split_keys = explode($this->separator(), $key);
        foreach ($split_keys as $k) {
            if (!isset($arr[$k])) {
                return false;
            }
            if (!is_array($arr[$k]) && ($arr[$k] instanceof ArrayAccess)) {
                return true;
            }
            $arr = $arr[$k];
        }
        return true;
    }

    public function set($key, $value)
    {
        $this[$key] = $value;
        return $this;
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
            throw new InvalidArgumentException(
                'Config array access only supports non-numeric keys.'
            );
        }
        $f = [$this, $offset];
        if (is_callable($f)) {
            return true;
        } else {
            return isset($this->{$offset});
        }
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
            throw new InvalidArgumentException(
                'Config array access only supports non-numeric keys.'
            );
        }
        $f = [$this, $offset];
        if (is_callable($f)) {
            return call_user_func($f);
        } else {
            return (isset($this->{$offset}) ? $this->{$offset} : null);
        }
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
            throw new InvalidArgumentException(
                'Config array access only supports non-numeric keys.'
            );
        }
        $f = [$this, 'set_'.$offset];
        if (is_callable($f)) {
            call_user_func($f, $value);
        } else {
            $this->{$offset} = $value;
        }
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
            throw new InvalidArgumentException(
                'Config array access only supports non-numeric keys.'
            );
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
            throw new InvalidArgumentException(
                'Config File must be a string.'
            );
        }
        if (!file_exists($filename)) {
            throw new InvalidArgumentException(
                'Config File does not exist'
            );
        }

        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if ($ext == 'php') {
            include $filename;
        } elseif ($ext == 'json') {
            $file_content = file_get_contents($filename);
            $config = json_decode($file_content, true);
            // Todo: check json error
            $this->set_data($config);

        } else {
            throw new InvalidArgumentException(
                'Only JSON and PHP files are accepted as a Config File.'
            );
        }

        return $this;
    }
}
