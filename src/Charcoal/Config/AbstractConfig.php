<?php

namespace Charcoal\Config;

use ArrayIterator;
use IteratorAggregate;
use Traversable;
use Throwable;
use Exception;
use LogicException;
use InvalidArgumentException;
use UnexpectedValueException;

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
     * @uses   self::offsetReplace()
     * @param  array|Traversable|ConfigInterface $data Key-value array of data to merge.
     * @return self
     */
    public function merge($data)
    {
        foreach ($data as $key => $value) {
            $this->offsetReplace($key, $value);
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
     * Replaces the value from the specified key.
     *
     * Routine:
     * - When the value in the Config and the new value are both arrays,
     *   the method will replace their respective value recursively.
     * - Then or otherwise, the new value is {@see self::offsetSet() assigned} to the Config.
     *
     * @uses   self::offsetSet()
     * @uses   array_replace_recursive()
     * @param  string $key   The data key to assign or merge $value to.
     * @param  mixed  $value The data value to assign to or merge with $key.
     * @throws InvalidArgumentException If the $key is not a string or is a numeric value.
     * @return void
     */
    public function offsetReplace($key, $value)
    {
        if (is_numeric($key)) {
            throw new InvalidArgumentException(
                'Entity array access only supports non-numeric keys'
            );
        }

        $key = $this->camelize($key);

        /** @internal Edge Case: "_" → "" */
        if ($key === '') {
            return;
        }

        if (is_array($value) && isset($this[$key])) {
            $data = $this[$key];
            if (is_array($data)) {
                $value = array_replace_recursive($data, $value);
            }
        }

        $this[$key] = $value;
    }

    /**
     * Add a configuration file to the configset.
     *
     * Supported file types are `ini`, `json`, `php`
     *
     * @param  string $path The file to load and add.
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
     * Load a configuration file as an array.
     *
     * Supported file types are `ini`, `json`, `php`
     *
     * @param  string $path A path to a supported file.
     * @throws InvalidArgumentException If the path is invalid.
     * @return array An associative array on success.
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
        switch ($ext) {
            case 'php':
                return $this->loadPhpFile($path);

            case 'json':
                return $this->loadJsonFile($path);

            case 'ini':
                return $this->loadIniFile($path);

            case 'yml':
            case 'yaml':
                return $this->loadYamlFile($path);
        }

        $validConfigExts = [ 'ini', 'json', 'php', 'yml' ];
        throw new InvalidArgumentException(sprintf(
            'Unsupported file format for "%s"; must be one of "%s"',
            $path,
            implode('", "', $validConfigExts)
        ));
    }

    /**
     * Load an INI file as an array.
     *
     * @param  string $path A path to an INI file.
     * @throws UnexpectedValueException If the file can not correctly be parsed into an array.
     * @return array An associative array on success.
     */
    private function loadIniFile($path)
    {
        $data = parse_ini_file($path, true);
        if ($data === false) {
            throw new UnexpectedValueException(
                sprintf('INI file "%s" is empty or invalid', $path)
            );
        }

        return $data;
    }

    /**
     * Load a JSON file as an array.
     *
     * @param  string $path A path to a JSON file.
     * @throws UnexpectedValueException If the file can not correctly be parsed into an array.
     * @return mixed Maybe an associative array on success.
     */
    private function loadJsonFile($path)
    {
        $data = null;
        $json = file_get_contents($path);
        if ($json) {
            $data = json_decode($json, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $error = json_last_error_msg() ?: 'Unknown error';
                throw new UnexpectedValueException(
                    sprintf('JSON file "%s" could not be parsed: %s', $path, $error)
                );
            }
        }

        return $data;
    }

    /**
     * Load a PHP file, maybe as an array.
     *
     * Note:
     * - The context of $this is bound to the current object.
     * - Data may be any value; the {@see self::addFile()} method will ignore
     *   anything that isn't an (associative) array.
     *
     * @param  string $path A path to a PHP file.
     * @throws UnexpectedValueException If the file can not correctly be parsed.
     * @return mixed Maybe an associative array on success.
     */
    private function loadPhpFile($path)
    {
        try {
            $data = include $path;
        } catch (Exception $e) {
            $message = sprintf('PHP file "%s" could not be parsed: %s', $path, $e->getMessage());
            throw new UnexpectedValueException($message, 0, $e);
        } catch (Throwable $e) {
            $message = sprintf('PHP file "%s" could not be parsed: %s', $path, $e->getMessage());
            throw new UnexpectedValueException($message, 0, $e);
        }

        return $data;
    }

    /**
     * Load a YAML file as an array.
     *
     * @param  string $path A path to a YAML/YML file.
     * @throws LogicException If a YAML parser is unavailable.
     * @throws UnexpectedValueException If the file can not correctly be parsed into an array.
     * @return array An associative array on success.
     */
    private function loadYamlFile($path)
    {
        if (!class_exists('Symfony\Component\Yaml\Parser')) {
            throw new LogicException('YAML format requires the Symfony YAML component');
        }

        try {
            $yaml = new YamlParser();
            $data = $yaml->parseFile($path);
        } catch (Exception $e) {
            $message = sprintf('YAML file "%s" could not be parsed: %s', $path, $e->getMessage());
            throw new UnexpectedValueException($message, 0, $e);
        }

        return $data;
    }
}
