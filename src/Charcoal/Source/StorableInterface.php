<?php

namespace Charcoal\Source;

// Local namespace dependencies
use \Charcoal\Source\SourceFactory;
use \Charcoal\Source\SourceInterface;

/**
* Storable items can be stored and loaded from a Source.
*/
interface StorableInterface
{
    /**
    * Set the object's ID. The actual property set depends on `key()`
    *
    * @param mixed $id The object's ID.
    * @return StorableInterface Chainable
    */
    public function setId($id);

    /**
    * Get the object's (unique) ID. The actualy property get depends on `key()`
    *
    * @return mixed
    */
    public function id();

    /**
    * Set the key property.
    *
    * @param string $key The object's key.
    * @return StorableInterface Chainable
    */
    public function setKey($key);

    /**
    * Get the key property.
    *
    * @return string
    */
    public function key();

    /**
     * @param SourceFactory $factory The source factory, which is useful to create source.
     * @return StorableInterface Chainable
     */
    public function setSourceFactory(SourceFactory $factory);

    /**
    * Set the object's source.
    *
    * @param SourceInterface $source The storable object's source.
    * @return StorableInterface Chainable
    */
    public function setSource(SourceInterface $source);

    /**
    * Get the object's source.
    *
    * @return SourceInterface
    */
    public function source();

    /**
    * Load an object from the database from its ID.
    *
    * @param mixed $id The ID of the object to load.
    * @return boolean Success / Failure
    */
    public function load($id);

    /**
    * Load an object from the database from its key $key.
    *
    * @param string $key   Key pointing a column's name.
    * @param mixed  $value Value of said column.
    * @return StorableInterface Chainable.
    */
    public function loadFrom($key = null, $value = null);

    /**
    * Load an object from the database from a custom SQL query.
    *
    * @param string $query The SQL query.
    * @param array  $binds Optional. The SQL query parameters.
    * @return StorableInterface Chainable.
    */
    public function loadFromQuery($query, array $binds = []);

    /**
    * Save an object current state to storage
    *
    * @return boolean
    */
    public function save();

    /**
    * Update the object in storage to the current object state.
    *
    * @param string[] $keys If set, only update the properties specified in this array.
    * @return boolean
    */
    public function update(array $keys = null);

    /**
    * Delete an object from storage.
    *
    * @return boolean
    */
    public function delete();
}
