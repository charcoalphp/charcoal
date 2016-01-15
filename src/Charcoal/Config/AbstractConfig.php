<?php

namespace Charcoal\Config;

// Dependencies from `PHP`
use \ArrayAccess;
use \ArrayIterator;
use \Exception;
use \InvalidArgumentException;
use \IteratorAggregate;
use \JsonSerializable;
use \Serializable;
use \Traversable;

// Dependencies from `container-interop/container-interop`
use Interop\Container\ContainerInterface;

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
    ArrayAccess,
    ConfigInterface,
    ContainerInterface,
    IteratorAggregate,
    JsonSerializable,
    Serializable
{
    const DEFAULT_SEPARATOR = '/';

    /**
     * Delimiter for accessing nested options.
     *
     * @var string $separator
     */
    private $separator = self::DEFAULT_SEPARATOR;

    /**
     * Delegates act as fallbacks when the current object
     * doesn't have a requested option.
     *
     * @var ConfigInterface[] $delegates
     */
    private $delegates = [];

    /**
     * Keep a list of all config keys available.
     * @var array $keys
     */
    private $keys = [];

    /**
     * Create the configuration.
     *
     * @param mixed $data Optional default data. Either a filename, an array, or a Config object.
     * @param ConfigInterface[] $delegates An array of delegates (config) to set.
     * @throws InvalidArgumentException If $data is invalid.
     */
    final public function __construct($data = null, array $delegates = null)
    {
        // Always set the default data first.
        $this->merge($this->defaults());

        // Set the delegates, if necessary.
        if (isset($delegates)) {
            $this->setDelegates($delegates);
        }

        if ($data === null) {
            return;
        }

        if (is_string($data)) {
            // Treat the parameter as a filename
            $this->addFile($data);
        } elseif (is_array($data)) {
            $this->merge($data);
        } elseif ($data instanceof ConfigInterface) {
            $this->merge($data);
        } else {
            throw new InvalidArgumentException(
                'Data must be an array, a file string or a ConfigInterface object.'
            );
        }


    }

    /**
     * Get the configuration's available keys.
     *
     * @return array
     */
    public function keys()
    {
        return array_keys($this->keys);
    }

    /**
     * @param ConfigInterface[] $delegates The array of delegates (config) to set.
     * @return ConfigInterface Chainable
     */
    public function setDelegates(array $delegates)
    {
        $this->delegates = [];
        foreach ($delegates as $delegate) {
            $this->addDelegate($delegate);
        }
        return $this;
    }

    /**
     * @param ConfigInterface[] $delegate A delegate (config) instance.
     * @return ConfigInterface Chainable
     */
    public function addDelegate(ConfigInterface $delegate)
    {
        $this->delegates[] = $delegate;
        return $this;
    }

    /**
     * @param ConfigInterface[] $delegate A delegate (config) instance.
     * @return ConfigInterface Chainable
     */
    public function prependDelegate(ConfigInterface $delegate)
    {
        array_unshift($this->delegates, $delegate);
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
     * @param string $separator A single-character to delimite nested options.
     * @throws InvalidArgumentException If $separator is invalid.
     * @return AbstractConfig Chainable
     */
    public function setSeparator($separator)
    {
        if (!is_string($separator)) {
            throw new InvalidArgumentException(
                'Separator needs to be a string.'
            );
        }
        // Question: should we use mb_strlen() here to allow unicode characters?
        if (strlen($separator) > 1) {
            throw new InvalidArgumentException(
                'Separator needs to be only one-character.'
            );
        }
        $this->separator = $separator;
        return $this;
    }



    /**
     * Add a configuration file. The file type is determined by its extension.
     *
     * Supported file types are `ini`, `json`, `php`
     *
     * @param string $filename A supported configuration file.
     * @throws InvalidArgumentException If the file is invalid.
     * @return AbstractConfig (Chainable)
     */
    public function addFile($filename)
    {
        $content = $this->loadFile($filename);
        if (is_array($content)) {
            $this->merge($content);
        }
        return $this;
    }

    /**
     * Load a configuration file. The file type is determined by its extension.
     *
     * Supported file types are `ini`, `json`, `php`
     *
     * @param string $filename A supported configuration file.
     * @throws InvalidArgumentException If the filename is invalid.
     * @return mixed The file content.
     */
    public function loadFile($filename)
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
            return $this->loadPhpFile($filename);
        } elseif ($ext == 'json') {
            return $this->loadJsonFile($filename);
        } elseif ($ext == 'ini') {
            return $this->loadIniFile($filename);
        } else {
            throw new InvalidArgumentException(
                'Only JSON, INI and PHP files are accepted as a Configuration file.'
            );
        }
    }

     /**
      * For each key, calls `set()`, which calls `offsetSet()`  (from ArrayAccess).
      *
      * The provided `$data` can be a simple array or an object which implements `Traversable`
      * (such as a `ConfigInterface` instance).
      *
      * @param array|Traversable $data The data to set.
      * @return AbstractConfig Chainable
      * @see self::set()
      * @see self::offsetSet()
      */
    public function merge($data)
    {
        foreach ($data as $k => $v) {
                $this->set($k, $v);
        }
        return $this;
    }

    /**
     * Get the configuration data, as an assoicative array map.
     *
     * @return array
     */
    public function data()
    {
        $ret = [];
        $keys = $this->keys();
        foreach ($keys as $k) {
            if ($this->has($k)) {
                $ret[$k] = $this[$k];
            }
        }
        return $ret;
    }

    /**
     * A stub for when the default data is empty.
     *
     * Make sure to reimplement in children ConfigInterface classes if any default data should be set.
     *
     * @see ConfigInterface::defaults()
     * @return array
     */
    public function defaults()
    {
        return [];
    }

    /**
     * Find an entry of the configuration by its key and retrieve it.
     *
     * @see self::offsetGet()
     * @param string $key The key of the configuration item to look for.
     * @return mixed
     */
    public function get($key)
    {
        return $this[$key];
    }



    /**
     * Assign a value to the specified key of the configuration.
     *
     * @see self::offsetSet()
     * @param string $key The key to assign $value to.
     * @param mixed $value Value to assign to $key.
     * @return AbstractConfig Chainable
     */
    public function set($key, $value)
    {
        $this[$key] = $value;
        return $this;
    }

    /**
     * Determine if a configuration key exists.
     *
     * @see self::offsetExists()
     * @param string $key The key of the configuration item to look for.
     * @return boolean
     */
    public function has($key)
    {
        return isset($this[$key]);
    }

    /**
     * JsonSerializable > jsonSerialize()
     *
     * @return array
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
     * @return void
     */
    public function unserialize($serialized)
    {
        $unserialized = unserialize($serialized);
        $this->merge($unserialized);
    }

    /**
     * Determine if a configuration key exists.
     *
     * @see ArrayAccess::offsetExists()
     * @param string $key The key of the configuration item to look for.
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

        if (strstr($key, $this->separator())) {
            return $this->hasWithSeparator($key);
        }

        $getter = $this->getter($key);
        if (is_callable([$this, $getter])) {
            $value = $this->{$getter}();
        } else {
            if (!isset($this->{$key})) {
                return $this->hasInDelegates($key);
            }
            $value = $this->{$key};
        }
        return ($value !== null);
    }

    /**
     * Find an entry of the configuration by its key and retrieve it.
     *
     * @see ArrayAccess::offsetGet()
     * @param string $key The key of the configuration item to look for.
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
        if (strstr($key, $this->separator())) {
            return $this->getWithSeparator($key);
        }
        $getter = $this->getter($key);
        if (is_callable([$this, $getter])) {
            return $this->{$getter}();
        } else {
            if (isset($this->{$key})) {
                return $this->{$key};
            } else {
                return $this->getInDelegates($key);
            }
        }
    }

    /**
     * Assign a value to the specified key of the configuration.
     *
     * Set the value either by:
     * - a setter method (`set_{$key}()`)
     * - setting (or overriding)
     *
     * @see ArrayAccess::offsetSet()
     * @param string $key The key to assign $value to.
     * @param mixed $value Value to assign to $key.
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

        if (strstr($key, $this->separator())) {
            return $this->setWithSeparator($key, $value);
        } else {
            $setter = $this->setter($key);
            if (is_callable([$this, $setter])) {
                $this->{$setter}($value);
            } else {
                $this->{$key} = $value;
            }
            $this->keys[$key] = true;
        }
    }

    /**
     * ArrayAccess > offsetUnset()
     *
     * @param string $key The key of the configuration item to remove.
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
        $this[$key] = null;
        unset($this->keys[$key]);
    }

    /**
     * IteratorAggregate > getIterator()
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->data());
    }

    /**
     * Allow an object to define how the key getter are called.
     *
     * @param string $key The key to get the getter from.
     * @param string $case Optional. The type of case to return. camel, pascal or snake.
     * @return string The getter method name, for a given key.
     */
    private function getter($key, $case = 'camel')
    {
        $getter = $key;

        if ($case == 'camel') {
            return $this->camelize($getter);
        } elseif ($case == 'pascal') {
            return $this->pascalize($getter);
        } else {
            return $getter;
        }
    }

    /**
     * Allow an object to define how the key setter are called.
     *
     * @param string $key The key to get the setter from.
     * @param string $case Optional. The type of case to return. camel, pascal or snake.
     * @return string The setter method name, for a given key.
     */
    private function setter($key, $case = 'camel')
    {
        $setter = 'set_'.$key;

        if ($case == 'camel') {
            return $this->camelize($setter);
        } elseif ($case == 'pascal') {
            return $this->pascalize($setter);
        } else {
            return $setter;
        }
    }

    /**
     * Transform a snake_case string to camelCase.
     *
     * @param string $str The snake_case string to camelize.
     * @return string The camelCase string.
     */
    private function camelize($str)
    {
        return lcfirst($this->pascalize($str));
    }

    /**
     * Transform a snake_case string to PamelCase.
     *
     * @param string $str The snake_case string to pascalize.
     * @return string The PamelCase string.
     */
    private function pascalize($str)
    {
        return implode('', array_map('ucfirst', explode('_', $str)));
    }

    /**
     * @param string $key The key of the configuration item to fetch.
     * @return mixed The item, if found, or null.
     */
    private function getInDelegates($key)
    {
        foreach ($this->delegates as $delegate) {
            if ($delegate->has($key)) {
                return $delegate->get($key);
            }
        }

        return null;
    }

    /**
     * @param string $key The key of the configuration item to check.
     */
    private function hasInDelegates($key)
    {
        foreach ($this->delegates as $delegate) {
            if ($delegate->has($key)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $key The key of the configuration item to look for.
     * @return mixed The value (or null)
     */
    private function getWithSeparator($key)
    {
        $arr = $this;
        $split_keys = explode($this->separator(), $key);
        foreach ($split_keys as $k) {
            if (!isset($arr[$k])) {
                return $this->getInDelegates($key);
            }
            if (!is_array($arr[$k]) && ($arr[$k] instanceof ArrayAccess)) {
                return $arr[$k];
            }
            $arr = $arr[$k];
        }
        return $arr;
    }

    /**
     * @param string $key The key of the configuration item to look for.
     * @return boolean
     */
    private function hasWithSeparator($key)
    {
        $arr = $this;
        $split_keys = explode($this->separator(), $key);
        foreach ($split_keys as $k) {
            if (!isset($arr[$k])) {
                return $this->hasInDelegates($key);
            }
            if (!is_array($arr[$k]) && ($arr[$k] instanceof ArrayAccess)) {
                return true;
            }
            $arr = $arr[$k];
        }
        return true;
    }

    /**
     * @param string $key The key to assign $value to.
     * @param mixed $value Value to assign to $key.
     * @throws Exception If a value already exists and is scalar (can not be merged).
     * @return void
     */
    private function setWithSeparator($key, $value)
    {
        $keys = explode($this->separator(), $key);
        $first = array_shift($keys);

        $lvl = 1;
        $num = count($keys);

        $source = $this[$first];

        $result = [];
        $ref = &$result;

        foreach ($keys as $p) {

            if ($lvl == $num) {
                $ref[$p] = $value;
            } else {
                if (!isset($source[$p])) {
                    $ref[$p] = [];
                } else {
                    if (is_array($source[$p]) || ($source[$p] instanceof ArrayAccess)) {
                        $ref[$p] = $source[$p];

                    } else {
                        throw new Exception(
                            sprintf('Can not set recursively with separator.')
                        );
                    }

                }
            }

            $ref = &$ref[$p];
            $lvl++;
        }

        // Merge, if necessary.
        if ($this->has($first)) {
            $result = ($this[$first] + $result);
        }

        $this[$first] = $result;
    }



    /**
     * Add a `.ini` file to the configuration.
     *
     * @param string $filename A INI configuration file.
     * @throws InvalidArgumentException If the file or invalid.
     * @return AbstractConfig Chainable
     */
    private function loadIniFile($filename)
    {
        $config = parse_ini_file($filename, true);
        if ($config === false) {
            throw new InvalidArgumentException(
                sprintf('Ini file "%s" is empty or invalid.')
            );
        }
        return $config;
    }

    /**
     * Add a `.json` file to the configuration.
     *
     * @param string $filename A JSON configuration file.
     * @throws InvalidArgumentException If the file or invalid.
     * @return AbstractConfig Chainable
     */
    private function loadJsonFile($filename)
    {
        $fileContent = file_get_contents($filename);
        $config = json_decode($fileContent, true);
        $errCode = json_last_error();
        if ($errCode == JSON_ERROR_NONE) {
            return $config;
        }
        // Handle JSON error
        switch ($errCode) {
            case JSON_ERROR_NONE:
                break;
            case JSON_ERROR_DEPTH:
                $errMsg = 'Maximum stack depth exceeded';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $errMsg = 'Underflow or the modes mismatch';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $errMsg = 'Unexpected control character found';
                break;
            case JSON_ERROR_SYNTAX:
                $errMsg = 'Syntax error, malformed JSON';
                break;
            case JSON_ERROR_UTF8:
                $errMsg = 'Malformed UTF-8 characters, possibly incorrectly encoded';
                break;
            default:
                $errMsg = 'Unknown error';
                break;
        }

        throw new InvalidArgumentException(
            sprintf('JSON file "%s" could not be parsed: "%s"', $filename, $errMsg)
        );

    }

    /**
     * Add a PHP file to the configuration
     *
     * @param string $filename A PHP configuration file.
     * @throws InvalidArgumentException If the file or invalid.
     * @return AbstractConfig Chainable
     */
    private function loadPhpFile($filename)
    {
        // `$this` is bound to the current configuration object (Current `$this`)
        $config = include $filename;
        return $config;
    }
}
