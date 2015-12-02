<?php

namespace Charcoal\Config;

// Dependencies from `PHP`
use \ArrayAccess;
use \InvalidArgumentException;

// Local namespace dependencies
use \Charcoal\Config\ConfigInterface;

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
    * @var string $separator
    */
    private $separator = self::DEFAULT_SEPARATOR;


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
        $this->separator = $separator;
        return $this;
    }

    /**
    * @return string
    */
    public function separator()
    {
        return $this->separator;
    }

    /**
    * For each key, calls `set()`, which calls `offsetSet()`  (from ArrayAccess)
    *
    * @param array $data
    * @return AbstractConfig Chainable
    * @see self::set()
    * @see self::offsetSet()
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
    * Make sure to reimplement in children Config classes if any default data should be set.
    * @return array
    */
    public function default_data()
    {
        return [];
    }

    /**
    * @param string $key
    * @return mixed
    * @see self::offsetSet()
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

    /**
    * @param string $key
    * @return mixed $value
    * @see offsetSet
    */
    public function set($key, $value)
    {
        $this[$key] = $value;
        return $this;
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
        if (is_callable([$this, $key])) {
            $value = $this->{$key}();
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
        $f = [$this, $key];
        if (is_callable([$this, $key])) {
            return $this->{$key}();
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
        if (is_callable([$this, 'set_'.$key])) {
            $this->{'set_'.$key}($value);
        } else {
            $this->{$key} = $value;
        }
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
                'Config array access only supports non-numeric keys.'
            );
        }
        if (is_callable([$this, 'set_'.$key])) {
            $this->{'set_'.$key}(null);
        } else {
            $this->{$key} = null;
        }
    }

    /**
    * Add a configuration file. The file type is determined by its extension.
    *
    * Supported file types are `ini`, `json`, `php`
    *
    * @param string $filename
    * @throws InvalidArgumentException if the filename is not a string or not valid json / php
    * @return AbstractConfig (Chainable)
    */
    public function add_file($filename)
    {
        if (!is_string($filename)) {
            throw new InvalidArgumentException(
                'Configuration file must be a string.'
            );
        }
        if (!file_exists($filename)) {
            throw new InvalidArgumentException(
                sprintf('Configuration file "%s" does not exist', $filename)
            );
        }

        $ext = pathinfo($filename, PATHINFO_EXTENSION);

        if ($ext == 'php') {
            return $this->add_php_file($filename);
        } elseif ($ext == 'json') {
            return $this->add_json_file($filename);
        } elseif ($ext == 'ini') {
            return $this->add_ini_file($filename);
        } else {
            throw new InvalidArgumentException(
                'Only JSON, INI and PHP files are accepted as a Configuration file.'
            );
        }
    }

    /**
    * Add a .ini file to the configuration
    *
    * @param string $filename
    * @throws InvalidArgumentException
    * @return AbstractConfig Chainable
    */
    private function add_ini_file($filename)
    {
        $config = parse_ini_file($filename, true);
        if ($config === false) {
            throw new InvalidArgumentException(
                sprintf('Ini file "%s" is empty or invalid.')
            );
        }
        $this->set_data($config);
        return $this;
    }

    /**
    * Add a .json file to the configuration
    *
    * @param string $filename
    * @throws InvalidArgumentException
    * @return AbstractConfig Chainable
    */
    private function add_json_file($filename)
    {
        $file_content = file_get_contents($filename);
        $config = json_decode($file_content, true);
        $err_code = json_last_error();
        if ($err_code == JSON_ERROR_NONE) {
            $this->set_data($config);
            return $this;
        }
        // Handle JSON error
        switch ($err_code) {
            case JSON_ERROR_NONE:
                break;
            case JSON_ERROR_DEPTH:
                $err_msg = 'Maximum stack depth exceeded';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $err_msg = 'Underflow or the modes mismatch';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $err_msg = 'Unexpected control character found';
                break;
            case JSON_ERROR_SYNTAX:
                $err_msg = 'Syntax error, malformed JSON';
                break;
            case JSON_ERROR_UTF8:
                $err_msg = 'Malformed UTF-8 characters, possibly incorrectly encoded';
                break;
            default:
                $err_msg = 'Unknown error';
                break;
        }

        throw new InvalidArgumentException(
            sprintf('JSON file "%s" could not be parsed: "%s"', $filename, $err_msg)
        );

    }

    /**
    * Add a .json file to the configuration
    *
    * @param string $filename
    * @throws InvalidArgumentException
    * @return AbstractConfig Chainable
    */
    private function add_php_file($filename)
    {
        // `$this` is bound to the current configuration object (Current `$this`)
        $config = include $filename;
        if (is_array($config)) {
            $this->set_data($config);
        }
        return $this;
    }
}
