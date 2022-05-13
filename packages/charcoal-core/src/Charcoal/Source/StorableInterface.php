<?php

namespace Charcoal\Source;

/**
 * Describes an object that can be stored and loaded from storage.
 */
interface StorableInterface
{
    /**
     * Set the object's unique ID.
     *
     * The actual property set depends on `key()`.
     *
     * @param  mixed $id The object's ID.
     * @throws \InvalidArgumentException If the argument is not scalar.
     * @return self
     */
    public function setId($id);

    /**
     * Get the object's unique ID.
     *
     * The actualy property get depends on `key()`.
     *
     * @return mixed
     */
    public function id();

    /**
     * Set the primary property key.
     *
     * For uniquely identifying this object in storage.
     *
     * @param  string $key The object's primary key.
     * @throws \InvalidArgumentException If the argument is not scalar.
     * @return StorableInterface Returns the current expression.
     */
    public function setKey($key);

    /**
     * Get the primary property key.
     *
     * @return string
     */
    public function key();

    /**
     * Get the object's datasource repository.
     *
     * @return SourceInterface
     */
    public function source();

    /**
     * Load an object from the repository from its ID.
     *
     * @param  mixed $id The ID of the object to load.
     * @return StorableInterface Returns the current expression.
     */
    public function load($id);

    /**
     * Load an object from the repository from its key $key.
     *
     * @param  string $key   Key pointing a column's name.
     * @param  mixed  $value Value of said column.
     * @return StorableInterface Returns the current expression.
     */
    public function loadFrom($key = null, $value = null);

    /**
     * Load an object from the repository from a custom SQL query.
     *
     * @param  string $query The SQL query.
     * @param  array  $binds Optional. The SQL query parameters.
     * @return StorableInterface Returns the current expression.
     */
    public function loadFromQuery($query, array $binds = []);

    /**
     * Insert the object's current state in storage.
     *
     * @return boolean TRUE on success.
     */
    public function save();

    /**
     * Update the object in storage with the current state.
     *
     * @param  string[] $keys If provided, only update the properties specified.
     * @return boolean TRUE on success.
     */
    public function update(array $keys = null);

    /**
     * Delete an object from storage.
     *
     * @return boolean TRUE on success.
     */
    public function delete();
}
