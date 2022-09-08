<?php

namespace Charcoal\Config;

use ArrayIterator;
use IteratorAggregate;
use Traversable;
use InvalidArgumentException;
// From PSR-11
use Psr\Container\ContainerInterface;

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
    use FileAwareTrait;
    use SeparatorAwareTrait;

    public const DEFAULT_SEPARATOR = '.';

    /**
     * Create the configuration.
     *
     * @param  mixed             $data      Initial data. Either a filepath,
     *     an associative array, or an {@see Traversable iterable object}.
     * @param  EntityInterface[] $delegates An array of delegates (config) to set.
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
        } elseif ($data instanceof Traversable) {
            $this->merge($data);
        } else {
            throw new InvalidArgumentException(sprintf(
                'Data must be a config file, an associative array, or an object implementing %s',
                Traversable::class
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
     * @uses   self::offsetReplace()
     * @param  array|Traversable $data Key-value dataset to merge.
     *     Either an associative array or an {@see Traversable iterable object}
     *     (such as {@see ConfigInterface}).
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

        $getter = 'get' . ucfirst($key);
        if (!isset($this->mutatorCache[$getter])) {
            $this->mutatorCache[$getter] = is_callable([ $this, $getter ]);
        }

        if ($this->mutatorCache[$getter]) {
            return ($this->{$getter}() !== null);
        }

        // -- START DEPRECATED
        if (!isset($this->mutatorCache[$key])) {
            $this->mutatorCache[$key] = is_callable([ $this, $key ]);
        }

        if ($this->mutatorCache[$key]) {
            return ($this->{$key}() !== null);
        }
        // -- END DEPRECATED

        if (isset($this->{$key})) {
            return true;
        }

        return $this->hasInDelegates($key);
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

        $getter = 'get' . ucfirst($key);
        if (!isset($this->mutatorCache[$getter])) {
            $this->mutatorCache[$getter] = is_callable([ $this, $getter ]);
        }

        if ($this->mutatorCache[$getter]) {
            return $this->{$getter}();
        }

        // -- START DEPRECATED
        if (!isset($this->mutatorCache[$key])) {
            $this->mutatorCache[$key] = is_callable([ $this, $key ]);
        }

        if ($this->mutatorCache[$key]) {
            return $this->{$key}();
        }
        // -- END DEPRECATED

        if (isset($this->{$key})) {
            return $this->{$key};
        }

        return $this->getInDelegates($key);
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

        $setter = 'set' . ucfirst($key);
        if (!isset($this->mutatorCache[$setter])) {
            $this->mutatorCache[$setter] = is_callable([ $this, $setter ]);
        }

        if ($this->mutatorCache[$setter]) {
            $this->{$setter}($value);
        } else {
            $this->{$key} = $value;
        }

        $this->keyCache[$key] = true;
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
     * Adds a configuration file to the configset.
     *
     * Natively supported file formats: INI, JSON, PHP.
     *
     * @uses   FileAwareTrait::loadFile()
     * @param  string $path The file to load and add.
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
}
