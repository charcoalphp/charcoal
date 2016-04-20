<?php

namespace Charcoal\Config;

// Dependencies from `PHP`
use \Exception;
use \InvalidArgumentException;

// Local namespace dependencies
use \Charcoal\Config\EntityInterface;

/**
 * Default Charcoal core entity (data container).
 */
abstract class AbstractEntity implements EntityInterface
{
    /**
     * Delegates act as fallbacks when the current object
     * doesn't have a requested option.
     *
     * @var EntityInterface[] $delegates
     */
    protected $delegates = [];

    /**
     * Keep a list of all config keys available.
     * @var array $keys
     */
    protected $keys = [];

    /**
     * Delimiter for accessing nested options.
     *
     * Is empty by default (which disables the separator feature).
     *
     * @var string $separator
     */
    protected $separator = '';

    /**
     * @param EntityInterface[] $delegates The array of delegates (config) to set.
     * @return EntityInterface Chainable
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
     * @param EntityInterface $delegate A delegate (config) instance.
     * @return EntityInterface Chainable
     */
    public function addDelegate(EntityInterface $delegate)
    {
        $this->delegates[] = $delegate;
        return $this;
    }

    /**
     * @param EntityInterface $delegate A delegate (config) instance.
     * @return EntityInterface Chainable
     */
    public function prependDelegate(EntityInterface $delegate)
    {
        array_unshift($this->delegates, $delegate);
        return $this;
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
        if (strlen($separator) > 1) {
            throw new InvalidArgumentException(
                'Separator needs to be only one-character, or empty.'
            );
        }
        $this->separator = $separator;
        return $this;
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
     * Gets the entity data, as associative array map.
     *
     * @param array $filters Optional. Property filters.
     * @return array The data map.
     */
    public function data(array $filters = null)
    {
        unset($filters);
        $ret = [];
        $keys = $this->keys();
        foreach ($keys as $k) {
            if ($k == 'data') {
                continue;
            }
            if (isset($this[$k])) {
                $ret[$k] = $this[$k];
            }
        }
        return $ret;
    }

    /**
     * Sets the entity data, from associative array map (or any other Traversable).
     *
     * This function takes an array and fill the property with its value.
     *
     * @param array|\Traversable $data The entity data. Will call setters.
     * @return EntityInterface Chainable
     * @see self::offsetSet()
     */
    public function setData($data)
    {
        foreach ($data as $prop => $val) {
            $this[$prop] = $val;
        }
        return $this;
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($key)` returning true does not mean that `get($key)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundException`.
     *
     * @param string $key Identifier of the entry to look for.
     * @return boolean
     */
    public function has($key)
    {
        return isset($this[$key]);
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
     * Public method variant of setting by array key.
     *
     * @see self::offsetSet()
     * @param string $key   The key to assign $value to.
     * @param mixed  $value Value to assign to $key.
     * @return AbstractConfig Chainable
     */
    public function set($key, $value)
    {
        $this[$key] = $value;
        return $this;
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
        $this->setData($unserialized);
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

        if ($this->separator && strstr($key, $this->separator)) {
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
        if ($this->separator && strstr($key, $this->separator)) {
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
     * @param string $key   The key to assign $value to.
     * @param mixed  $value Value to assign to $key.
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

        if ($this->separator && strstr($key, $this->separator)) {
            $this->setWithSeparator($key, $value);
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
         * @param string $key The key of the configuration item to fetch.
         * @return mixed The item, if found, or null.
         */
    private function getInDelegates($key)
    {
        foreach ($this->delegates as $delegate) {
            if (isset($delegate[$key])) {
                return $delegate[$key];
            }
        }

        return null;
    }

    /**
     * @param string $key The key of the configuration item to check.
     * @return boolean
     */
    protected function hasInDelegates($key)
    {
        foreach ($this->delegates as $delegate) {
            if (isset($delegate[$key])) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $key The key of the configuration item to look for.
     * @return mixed The value (or null)
     */
    protected function getWithSeparator($key)
    {
        $arr = $this;
        $split_keys = explode($this->separator, $key);
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
    protected function hasWithSeparator($key)
    {
        $arr = $this;
        $split_keys = explode($this->separator, $key);
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
     * @param string $key   The key to assign $value to.
     * @param mixed  $value Value to assign to $key.
     * @throws Exception If a value already exists and is scalar (can not be merged).
     * @return void
     */
    protected function setWithSeparator($key, $value)
    {
        $keys = explode($this->separator, $key);
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
        if (isset($this[$first])) {
            $result = ($this[$first] + $result);
        }

        $this[$first] = $result;
    }

    /**
     * Allow an object to define how the key getter are called.
     *
     * @param string $key The key to get the getter from.
     * @return string The getter method name, for a given key.
     */
    protected function getter($key)
    {
        $getter = $key;
        return $this->camelize($getter);
    }

    /**
     * Allow an object to define how the key setter are called.
     *
     * @param string $key The key to get the setter from.
     * @return string The setter method name, for a given key.
     */
    protected function setter($key)
    {
        $setter = 'set_'.$key;
        return $this->camelize($setter);
    }

    /**
     * Transform a snake_case string to camelCase.
     *
     * @param string $str The snake_case string to camelize.
     * @return string The camelcase'd string.
     */
    protected function camelize($str)
    {
        return lcfirst(implode('', array_map('ucfirst', explode('_', $str))));
    }
}
