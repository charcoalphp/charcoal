<?php

namespace Charcoal\Config;

use ArrayAccess;
use JsonSerializable;
use Serializable;

/**
 * Describes a conceptual data model.
 */
interface EntityInterface extends
    ArrayAccess,
    JsonSerializable,
    Serializable
{
    /**
     * Gets the data keys on this entity.
     *
     * @return array
     */
    public function keys();

    /**
     * Gets all data, or a subset, from this entity.
     *
     * @param  string[] $keys Optional. Extracts only the requested data.
     * @return array An associative array.
     */
    public function data(array $keys = null);

    /**
     * Sets data on this entity.
     *
     * @param  array $data An associative array.
     * @return EntityInterface Chainable
     */
    public function setData(array $data);

    /**
     * Determines if this entity contains the specified key and if its value is not NULL.
     *
     * @param  string $key The data key to check.
     * @return boolean TRUE if $key exists and has a value other than NULL, FALSE otherwise.
     */
    public function has($key);

    /**
     * Find an entry of the configuration by its key and retrieve it.
     *
     * @param  string $key The data key to retrieve.
     * @return mixed Value of the requested $key on success, NULL if the $key is not set.
     */
    public function get($key);

    /**
     * Assign a value to the specified key on this entity.
     *
     * @param  string $key   The data key to assign $value to.
     * @param  mixed  $value The data value to assign to $key.
     * @return EntityInterface Chainable
     */
    public function set($key, $value);
}
