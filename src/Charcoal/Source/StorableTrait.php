<?php

namespace Charcoal\Source;

// Local namespace dependencies
use \Charcoal\Source\SourceInterface;

/**
* Full implementation, as trait, of the StorableInterface
*/
trait StorableTrait
{
    /**
    * @var SourceInterfae $source
    */
    private $source;

    /**
    * Set the object's source.
    *
    * @param SourceInterface $source
    * @return StorableInterface Chainable
    */
    public function set_source(SourceInterface $source)
    {
        $this->source = $source;
        return $this;
    }

    /**
    * Get the object's source.
    *
    * @return SourceInterface
    */
    public function source()
    {
        if ($this->source === null) {
            $this->source = $this->create_source();
        }
        return $this->source;
    }

    /**
    * @param mixed $data Optional
    * @return SourceInterface
    */
    abstract public function create_source($data = null);

    /**
    * Load an object from the database from its ID.
    *
    * Note that the object should also implement `Charcoal\Model\IndexableInterface`
    * (provide an `id()` and `key()` methods) for this function to work properly.
    *
    * @param mixed $id The i
    * @return StorableInterface Chainable
    */
    public function load($id = null)
    {
        if ($id === null) {
            $id = $this->id();
        }
        $this->source()->load_item($id, $this);
        return $this;
    }

    /**
    * Load an object from the database from its key $key.
    *
    * Note that the object should also implement `Charcoal\Model\IndexableInterface`
    * (provide an `id()` and `key()` methods) for this function to work properly.
    *
    * @param string $key Key pointing a column's name
    * @param mixed $value Value of said column
    * @return StorableInterface Chainable
    */
    public function load_from($key = null, $value = null)
    {
        $this->source()->load_item_from_key($key, $value, $this);
        return $this;
    }

    /**
    * Save an object current state to storage
    *
    * @return boolean
    */
    public function save()
    {
        $pre = $this->pre_save();
        if ($pre === false) {
            return false;
        }
        $ret = $this->source()->save_item($this);
        $this->post_save();
        return $ret;
    }

    /**
    * Update the object in storage to the current object state.
    *
    * @param array $properties If set, only update the properties specified in this array.
    * @return boolean
    */
    public function update($properties = null)
    {
        $pre = $this->pre_update($properties);
        $this->save_properties($properties);
        if ($pre === false) {
            return false;
        }
        $ret = $this->source()->update_item($this, $properties);
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
        $pre = $this->pre_delete();
        if ($pre === false) {
            return false;
        }
        $ret = $this->source()->delete_item($this);
        $this->post_delete();
        return $ret;
    }

    /**
    * @return boolean
    */
    abstract protected function pre_save();
    /**
    * @return boolean
    */
    abstract protected function post_save();
    /**
    * @param array $properties
    * @return boolean
    */
    abstract protected function pre_update($properties = null);
    /**
    * @param array $properties
    * @return boolean
    */
    abstract protected function post_update($properties = null);
    /**
    * @return boolean
    */
    abstract protected function pre_delete();
    /**
    * @return boolean
    */
    abstract protected function post_delete();
}
