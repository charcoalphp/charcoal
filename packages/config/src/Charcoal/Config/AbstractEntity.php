<?php

namespace Charcoal\Config;

use ArrayAccess;
use InvalidArgumentException;

/**
 * Default data model.
 *
 * ### Notes on {@see \ArrayAccess}:
 *
 * - Keys SHOULD be formatted as "snake_case" (e.g., "first_name") or "camelCase" (e.g., "firstName").
 *   and WILL be converted to the latter {@see PSR-1} to access or assign values.
 * - Values are accessed and assigned via methods and properties which MUST be formatted as "camelCase",
 *   e.g.: `$firstName`, `firstName()`, `setFirstName()`.
 * - A key-value pair is internally passed to a (non-private / non-static) setter method (if present)
 *   or assigned to a (non-private / non-static) property (declared or not) and tracks affected keys.
 */
abstract class AbstractEntity implements EntityInterface
{
    /**
     * Holds a list of all data keys per class.
     *
     * @var (boolean|null)[]
     */
    protected $keyCache = [];

    /**
     * Holds a list of getters/setters per class.
     *
     * @var string[]
     */
    protected $mutatorCache = [];

    /**
     * Holds a list of all camelized strings.
     *
     * @var string[]
     */
    protected static $camelCache = [];

    /**
     * Gets the data keys on this entity.
     *
     * @return array
     */
    public function keys()
    {
        return array_keys($this->keyCache);
    }

    /**
     * Gets all data, or a subset, from this entity.
     *
     * @uses   self::offsetExists()
     * @uses   self::offsetGet()
     * @param  string[] $keys Optional. Extracts only the requested data.
     * @return array Key-value array of data, excluding pairs with NULL values.
     */
    public function data(array $keys = null)
    {
        if ($keys === null) {
            $keys = $this->keys();
        }

        $data = [];
        foreach ($keys as $key) {
            if (strtolower($key) === 'data') {
                /** @internal Edge Case: Avoid recursive call */
                continue;
            }

            if (isset($this[$key])) {
                $data[$key] = $this[$key];
            }
        }
        return $data;
    }

    /**
     * Sets data on this entity.
     *
     * @uses   self::offsetSet()
     * @param  array $data Key-value array of data to append.
     * @return self
     */
    public function setData(array $data)
    {
        foreach ($data as $key => $value) {
            if (strtolower($key) === 'data') {
                /** @internal Edge Case: Avoid recursive call */
                continue;
            }

            $this[$key] = $value;
        }
        return $this;
    }

    /**
     * Determines if this entity contains the specified key and if its value is not NULL.
     *
     * @uses   self::offsetExists()
     * @param  string $key The data key to check.
     * @return boolean TRUE if $key exists and has a value other than NULL, FALSE otherwise.
     */
    public function has($key)
    {
        return isset($this[$key]);
    }

    /**
     * Find an entry of the configuration by its key and retrieve it.
     *
     * @uses   self::offsetGet()
     * @param  string $key The data key to retrieve.
     * @return mixed Value of the requested $key on success, NULL if the $key is not set.
     */
    public function get($key)
    {
        return $this[$key];
    }

    /**
     * Assign a value to the specified key on this entity.
     *
     * @uses   self::offsetSet()
     * @param  string $key   The data key to assign $value to.
     * @param  mixed  $value The data value to assign to $key.
     * @return self Chainable
     */
    public function set($key, $value)
    {
        $this[$key] = $value;
        return $this;
    }

    /**
     * Determines if this entity contains the specified key and if its value is not NULL.
     *
     * Routine:
     * - If the entity has a getter method (e.g., "foo_bar" → `fooBar()`),
     *   its called and its value is checked;
     * - If the entity has a property (e.g., `$fooBar`), its value is checked;
     * - If the entity has neither, FALSE is returned.
     *
     * @see    \ArrayAccess
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

        return false;
    }

    /**
     * Returns the value from the specified key on this entity.
     *
     * Routine:
     * - If the entity has a getter method (e.g., "foo_bar" → `fooBar()`),
     *   its called and returns its value;
     * - If the entity has a property (e.g., `$fooBar`), its value is returned;
     * - If the entity has neither, NULL is returned.
     *
     * @see    \ArrayAccess
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

        return null;
    }

    /**
     * Assigns the value to the specified key on this entity.
     *
     * Routine:
     * - The data key is added to the {@see self::$keys entity's key pool}.
     * - If the entity has a setter method (e.g., "foo_bar" → `setFooBar()`),
     *   its called and passed the value;
     * - If the entity has NO setter method, the value is assigned to a property (e.g., `$fooBar`).
     *
     * @see    \ArrayAccess
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
     * Removes the value from the specified key on this entity.
     *
     * Routine:
     * - The data key is removed from the {@see self::$keys entity's key pool}.
     * - NULL is {@see self::offsetSet() assigned} to the entity.
     *
     * @see    \ArrayAccess
     * @uses   self::offsetSet()
     * @param  string $key The data key to remove.
     * @throws InvalidArgumentException If the $key is not a string or is a numeric value.
     * @return void
     */
    public function offsetUnset($key)
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

        $this[$key] = null;
        unset($this->keyCache[$key]);
    }

    /**
     * Gets the data that can be serialized with {@see json_encode()}.
     *
     * @see    \JsonSerializable
     * @return array Key-value array of data.
     */
    public function jsonSerialize()
    {
        return $this->data();
    }

    /**
     * Serializes the data on this entity.
     *
     * @see    \Serializable
     * @return string Returns a string containing a byte-stream representation of the object.
     */
    public function serialize()
    {
        return serialize($this->data());
    }

    /**
     * Applies the serialized data to this entity.
     *
     * @see    \Serializable
     * @param  string $data The serialized data to extract.
     * @return void
     */
    public function unserialize($data)
    {
        $data = unserialize($data);
        $this->setData($data);
    }

    /**
     * Transform a string from "snake_case" to "camelCase".
     *
     * @param  string $value The string to camelize.
     * @return string The camelized string.
     */
    final protected function camelize($value)
    {
        $key = $value;

        if (isset(static::$camelCache[$key])) {
            return static::$camelCache[$key];
        }

        if (strpos($value, '_') !== false) {
            $value = implode('', array_map('ucfirst', explode('_', $value)));
        }

        static::$camelCache[$key] = lcfirst($value);

        return static::$camelCache[$key];
    }
}
