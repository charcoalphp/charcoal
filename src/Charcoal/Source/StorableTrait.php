<?php

namespace Charcoal\Source;

use \Charcoal\Source\SourceInterface as SourceInterface;
use \Charcoal\Source\StorableInterface as StorableInterface;

trait StorableTrait
{
    private $_source;
    /**
    * Set the object's source.
    *
    * @param SourceInterface $source
    * @return StorableInterface Chainable
    */
    public function set_source(SourceInterface $source)
    {
        $this->_source = $source;
        return $this;
    }
    
    /**
    * Get the object's source.
    *
    * @return SourceInterface
    */
    public function source()
    {
        return $this->_source;
    }
    
    /**
    * Load an object from the database from its ID.
    *
    * Note that the object should also implement `Charcoal\Model\IndexableInterface`
    * (provide an `id()` and `key()` methods) for this function to work properly.
    *
    * @param mixed $id The i
    * @return boolean Success / Failure
    */
    public function load($id)
    {
        $ret = $this->source()->load_data($id);
        $this->set_flat_data($ret);
        return $ret;
    }

    /**
    * Save an object current state to storage
    *
    * @return boolean
    */
    public function save()
    {
        $this->pre_save();
        $ret = $this->source()->save();
        $this->post_save();
        return $ret;
    }

    /**
    * Update the object in storage to the current object state.
    *
    * @param array $properties If set, only update the properties specified in this array.
    * @return boolean
    */
    public function update($properties=null)
    {
        $this->pre_update($properties);
        $ret = $this->source()->update($properties);
        $this->post_update($properties);
        return $ret;
    }

    /**
    * Delete an object from storage.
    *
    * @return boolean
    */
    public function delete()
    {
        $this->pre_delete();
        $ret = $this->source()->delete();
        $this->post_delete();
        return $ret;
    }

    abstract protected function pre_save();
    abstract protected function post_save();
    abstract protected function pre_update($properties=null);
    abstract protected function post_update($properties=null);
    abstract protected function pre_delete();
    abstract protected function post_delete();
}
