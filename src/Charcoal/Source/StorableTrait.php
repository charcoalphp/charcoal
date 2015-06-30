<?php

namespace Charcoal\Source;

use \InvalidArgumentException as InvalidArgumentException;

use \Charcoal\Source\SourceInterface as SourceInterface;
use \Charcoal\Source\StorableInterface as StorableInterface;

trait StorableTrait
{
    /**
    * @var SourceInterfae $_source
    */
    private $_source;

    /**
    * @param array $data
    * @throws InvalidArgumentException
    * @return StorableInterface Chainable
    */
    public function set_storable_data($data)
    {
        if (!is_array($data)) {
            throw new InvalidArgumentException('Data must be an array');
        }
        if (isset($data['source']) && $data['source'] !== null) {
            $this->set_source($data['source']);
        }
        return $this;
    }

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
        if ($this->_source === null) {
            $this->_source = $this->create_source();
        }
        return $this->_source;
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
    * @return bool
    */
    abstract protected function pre_save();
    /**
    * @return bool
    */
    abstract protected function post_save();
    /**
    * @param array $properties
    * @return bool
    */
    abstract protected function pre_update($properties = null);
    /**
    * @param array $properties
    * @return bool
    */
    abstract protected function post_update($properties = null);
    /**
    * @return bool
    */
    abstract protected function pre_delete();
    /**
    * @return bool
    */
    abstract protected function post_delete();
}
