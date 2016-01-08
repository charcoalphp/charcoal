<?php

namespace Charcoal\Source;

// Local namespace dependencies
use \Charcoal\Source\SourceInterface as SourceInterface;

/**
* Storable items can be stored and loaded from a Source.
*/
interface StorableInterface
{
    /**
    * Set the object's source.
    *
    * @param SourceInterface $source
    * @return StorableInterface Chainable
    */
    public function set_source(SourceInterface $source);

    /**
    * Get the object's source.
    *
    * @return SourceInterface
    */
    public function source();

    /**
    * Load an object from the database from its ID.
    *
    * Note that the object should also implement `Charcoal\Model\IndexableInterface`
    * (provide an `id()` and `key()` methods) for this function to work properly.
    *
    * @param mixed $id The i
    * @return boolean Success / Failure
    */
    public function load($id);

    /**
    * Load an object from the database from its key $key.
    *
    * Note that the object should also implement `Charcoal\Model\IndexableInterface`
    * (provide an `id()` and `key()` methods) for this function to work properly.
    *
    * @param string $key Key pointing a column's name
    * @param mixed $value Value of said column
    * @return StorableInterface Chainable.
    */
    public function load_from($key = null, $value = null);

    /**
    * Load an object from the database from a custom SQL query.
    *
    * @param string $query The SQL query.
    * @param array $binds Optional. The SQL query parameters.
    * @return StorableInterface Chainable.
    */
    public function load_from_query($query, array $binds = null);

    /**
    * Save an object current state to storage
    *
    * @return boolean
    */
    public function save();

    /**
    * Update the object in storage to the current object state.
    *
    * @param array $properties If set, only update the properties specified in this array.
    * @return boolean
    */
    public function update($properties = null);

    /**
    * Delete an object from storage.
    *
    * @return boolean
    */
    public function delete();
}
