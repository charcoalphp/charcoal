<?php

namespace Charcoal\Source;

use \Charcoal\Source\SourceInterface as SourceInterface;

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
    public function update($properties=null);

    /**
    * Delete an object from storage.
    *
    * @return boolean
    */
    public function delete();
}
