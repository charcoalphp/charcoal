<?php

namespace Charcoal\Config;

// Dependencies from `PHP`
use ArrayAccess;
use InvalidArgumentException;

/**
 * Default Charcoal core entity (data container).
 */
abstract class AbstractEntity implements EntityInterface
{

    /**
     * Keep a list of all config keys available.
     * @var array $keys
     */
    protected $keys = [];

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
                // Avoid recursive call
                continue;
            }
            if (isset($this[$k])) {
                $ret[$k] = $this[$k];
            }
        }
        return $ret;
    }

    /**
     * Sets the entity data, from associative array map.
     *
     * This function takes an array and fill the property with its value.
     *
     * @param array $data The entity data. Will call setters.
     * @return self
     * @see self::offsetSet()
     */
    public function setData(array $data)
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
     * @return self
     */
    public function set($key, $value)
    {
        $this[$key] = $value;
        return $this;
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
                'Entity array access only supports non-numeric keys.'
            );
        }

        $key = $this->camelize($key);
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
                'Entity array access only supports non-numeric keys.'
            );
        }

        $key = $this->camelize($key);

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
                'Entity array access only supports non-numeric keys.'
            );
        }

        $key = $this->camelize($key);
        $setter = 'set'.ucfirst($key);

        // Case: url.com?_=something
        if ($setter === 'set') {
            return;
        }

        if (is_callable([$this, $setter])) {
            $this->{$setter}($value);
        } else {
            $this->{$key} = $value;
        }
        $this->keys[$key] = true;
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
                'Entity array access only supports non-numeric keys.'
            );
        }
        $key = $this->camelize($key);
        $this[$key] = null;
        unset($this->keys[$key]);
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
     * Transform a snake_case string to camelCase.
     *
     * @param string $str The snake_case string to camelize.
     * @return string The camelcase'd string.
     */
    final protected function camelize($str)
    {
        if (strstr($str, '_') === false) {
            return $str;
        }
        return lcfirst(implode('', array_map('ucfirst', explode('_', $str))));
    }
}
