<?php

namespace Charcoal\Config;

use ArrayIterator;
use IteratorAggregate;
use Traversable;
use InvalidArgumentException;

// From 'symfony/yaml'
use Symfony\Component\Yaml\Parser as YamlParser;

// From 'container-interop/container-interop'
use Interop\Container\ContainerInterface;

/**
 * Default configuration container / registry.
 *
 * ### Notes on {@see SeparatorAwareTrait}:
 *
 * - Provides the ability for a store to fetch data that is nested in a tree-like structure,
 *   often referred to as "dot" notation.
 *
 * ### Notes on {@see DelegatesAwareTrait}:
 *
 * - Provides the ability for a store to fetch data in another store.
 * - Provides this store with a way to register one or more delegate stores.
 */
abstract class AbstractConfig extends AbstractEntity implements
    ConfigInterface,
    ContainerInterface,
    IteratorAggregate
{
    use DelegatesAwareTrait;
    use SeparatorAwareTrait;

    const DEFAULT_SEPARATOR = '.';

    /**
     * Create the configuration.
     *
     * @param  mixed             $data      Initial data. Either a filepath, a datamap, or a Config object.
     * @param  ConfigInterface[] $delegates An array of delegates (config) to set.
     * @throws InvalidArgumentException If $data is invalid.
     */
    final public function __construct($data = null, array $delegates = null)
    {
        // Always set the default chaining notation
        $this->setSeparator(self::DEFAULT_SEPARATOR);

        // Always set the default data first.
        $this->setData($this->defaults());

        // Set the delegates, if necessary.
        if (isset($delegates)) {
            $this->setDelegates($delegates);
        }

        if ($data === null) {
            return;
        }

        if (is_string($data)) {
            // Treat the parameter as a filepath
            $this->addFile($data);
        } elseif (is_array($data)) {
            $this->merge($data);
        } elseif ($data instanceof ConfigInterface) {
            $this->merge($data);
        } else {
            throw new InvalidArgumentException(sprintf(
                'Data must be an associative array, a file path, or an instance of %s',
                ConfigInterface::class
            ));
        }
    }

    /**
     * Gets all default data from this store.
     *
     * Pre-populates new stores.
     *
     * May be reimplemented in inherited classes if any default values should be defined.
     *
     * @return array Key-value array of data
     */
    public function defaults()
    {
        return [];
    }

    /**
     * Adds new data, replacing / merging existing data with the same key.
     *
     * The provided `$data` can be a simple array or an object which implements `Traversable`
     * (such as a `ConfigInterface` instance).
     *
     * @param  array|Traversable|ConfigInterface $data Key-value array of data to merge.
     * @return self
     */
    public function merge($data)
    {
        foreach ($data as $k => $v) {
            if (is_array($v) && isset($this[$k]) && is_array($this[$k])) {
                $v = array_replace_recursive($this[$k], $v);
            }
            $this[$k] = $v;
        }
        return $this;
    }

    /**
     * Create a new iterator from the configuration instance.
     *
     * @see    IteratorAggregate
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->data());
    }

    /**
     * Determines if this store contains the specified key and if its value is not NULL.
     *
     * Routine:
     * - If the data key is {@see SeparatorAwareTrait::$separator nested},
     *   the data-tree is traversed until the endpoint is found, if any;
     * - If the data key does NOT exist on the store, a lookup is performed
     *   on each delegate store until a key is found, if any.
     *
     * @see    \ArrayAccess
     * @uses   SeparatorAwareTrait::hasWithSeparator()
     * @uses   DelegatesAwareTrait::hasInDelegates()
     * @param  string $key The data key to check.
     * @throws InvalidArgumentException If the $key is not a string or is a numeric value.
     * @return boolean TRUE if $key exists and has a value other than NULL, FALSE otherwise.
     */
    public function offsetExists($key)
    {
        if (is_numeric($key)) {
            throw new InvalidArgumentException(
                'Entity array access only supports non-numeric keys'
            );
        }

        if ($this->separator && strstr($key, $this->separator)) {
            return $this->hasWithSeparator($key);
        }

        $key = $this->camelize($key);

        /** @internal Edge Case: "_" → "" */
        if ($key === '') {
            return false;
        }

        if (is_callable([ $this, $key ])) {
            $value = $this->{$key}();
        } else {
            if (!isset($this->{$key})) {
                return $this->hasInDelegates($key);
            }
            $value = $this->{$key};
        }

        return ($value !== null);
    }

    /**
     * Returns the value from the specified key on this entity.
     *
     * Routine:
     * - If the data key is {@see SeparatorAwareTrait::$separator nested},
     *   the data-tree is traversed until the endpoint to return its value, if any;
     * - If the data key does NOT exist on the store, a lookup is performed
     *   on each delegate store until a value is found, if any.
     *
     * @see    \ArrayAccess
     * @uses   SeparatorAwareTrait::getWithSeparator()
     * @uses   DelegatesAwareTrait::getInDelegates()
     * @param  string $key The data key to retrieve.
     * @throws InvalidArgumentException If the $key is not a string or is a numeric value.
     * @return mixed Value of the requested $key on success, NULL if the $key is not set.
     */
    public function offsetGet($key)
    {
        if (is_numeric($key)) {
            throw new InvalidArgumentException(
                'Entity array access only supports non-numeric keys'
            );
        }

        if ($this->separator && strstr($key, $this->separator)) {
            return $this->getWithSeparator($key);
        }

        $key = $this->camelize($key);

        /** @internal Edge Case: "_" → "" */
        if ($key === '') {
            return null;
        }

        if (is_callable([ $this, $key ])) {
            return $this->{$key}();
        } else {
            if (isset($this->{$key})) {
                return $this->{$key};
            } else {
                return $this->getInDelegates($key);
            }
        }
    }

    /**
     * Assigns the value to the specified key on this entity.
     *
     * Routine:
     * - If the data key is {@see SeparatorAwareTrait::$separator nested},
     *   the data-tree is traversed until the endpoint to assign its value;
     *
     * @see    \ArrayAccess
     * @uses   SeparatorAwareTrait::setWithSeparator()
     * @param  string $key   The data key to assign $value to.
     * @param  mixed  $value The data value to assign to $key.
     * @throws InvalidArgumentException If the $key is not a string or is a numeric value.
     * @return void
     */
    public function offsetSet($key, $value)
    {
        if (is_numeric($key)) {
            throw new InvalidArgumentException(
                'Entity array access only supports non-numeric keys'
            );
        }

        if ($this->separator && strstr($key, $this->separator)) {
            $this->setWithSeparator($key, $value);
            return;
        }

        $key = $this->camelize($key);

        /** @internal Edge Case: "_" → "" */
        if ($key === '') {
            return;
        }

        $setter = 'set'.ucfirst($key);
        if (is_callable([ $this, $setter ])) {
            $this->{$setter}($value);
        } else {
            $this->{$key} = $value;
        }

        $this->keys[$key] = true;
    }

    /**
     * Add a configuration file. The file type is determined by its extension.
     *
     * Supported file types are `ini`, `json`, `php`
     *
     * @param  string $path A supported configuration file.
     * @throws InvalidArgumentException If the file is invalid.
     * @return self
     */
    public function addFile($path)
    {
        $config = $this->loadFile($path);
        if (is_array($config)) {
            $this->merge($config);
        }
        return $this;
    }

    /**
     * Load a configuration file. The file type is determined by its extension.
     *
     * Supported file types are `ini`, `json`, `php`
     *
     * @param  string $path A supported configuration file.
     * @throws InvalidArgumentException If the filename is invalid.
     * @return mixed
     */
    public function loadFile($path)
    {
        if (!is_string($path)) {
            throw new InvalidArgumentException(
                'Configuration file must be a string'
            );
        }
        if (!file_exists($path)) {
            throw new InvalidArgumentException(
                sprintf('Configuration file "%s" does not exist', $path)
            );
        }

        $ext = pathinfo($path, PATHINFO_EXTENSION);

        if ($ext === 'php') {
            return $this->loadPhpFile($path);
        } elseif ($ext === 'json') {
            return $this->loadJsonFile($path);
        } elseif ($ext === 'ini') {
            return $this->loadIniFile($path);
        } elseif ($ext === 'yml' || $ext === 'yaml') {
            return $this->loadYamlFile($path);
        } else {
            $validConfigExts = [ 'php', 'json', 'yml', 'ini' ];
            throw new InvalidArgumentException(sprintf(
                'Unsupported configuration file; must be one of "%s", received "%s"',
                implode('","', $validConfigExts),
                $ext
            ));
        }
    }

    /**
     * Add a `.ini` file to the configuration.
     *
     * @param  string $path A INI configuration file.
     * @throws InvalidArgumentException If the file or invalid.
     * @return mixed
     */
    private function loadIniFile($path)
    {
        $config = parse_ini_file($path, true);
        if ($config === false) {
            throw new InvalidArgumentException(
                sprintf('Ini file "%s" is empty or invalid', $path)
            );
        }
        return $config;
    }

    /**
     * Add a `.json` file to the configuration.
     *
     * @param  string $path A JSON configuration file.
     * @throws InvalidArgumentException If the file or invalid.
     * @return mixed
     */
    private function loadJsonFile($path)
    {
        $config  = file_get_contents($path);
        $config  = json_decode($config, true);
        $errCode = json_last_error();
        if ($errCode == JSON_ERROR_NONE) {
            return $config;
        }

        // Handle JSON error
        $errMsg = '';
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
            sprintf('JSON file "%s" could not be parsed: "%s"', $path, $errMsg)
        );
    }

    /**
     * Add a PHP file to the configuration
     *
     * @param  string $path A PHP configuration file.
     * @return mixed
     */
    private function loadPhpFile($path)
    {
        // `$this` is bound to the current configuration object (Current `$this`)
        $config = include $path;
        return $config;
    }

    /**
     * Add a YAML file to the configuration
     *
     * @param  string $path A YAML configuration file.
     * @throws InvalidArgumentException If the YAML file can not correctly be parsed into an array.
     * @return mixed
     */
    private function loadYamlFile($path)
    {
        $parser = new YamlParser();
        $config = file_get_contents($path);
        $config = $parser->parse($config);
        if (!is_array($config)) {
            throw new InvalidArgumentException(
                sprintf('YAML file "%s" could not be parsed (invalid yaml)', $path)
            );
        }
        return $config;
    }
}
