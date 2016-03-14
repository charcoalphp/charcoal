<?php

namespace Charcoal\Source;

// Local namespace dependencies
use \Charcoal\Source\SourceFactory;
use \Charcoal\Source\SourceInterface;

/**
* Full implementation, as trait, of the StorableInterface
*/
trait StorableTrait
{
    /**
    * @var SourceFactory $sourceFactory
    */
    protected $sourceFactory;

    /**
    * @var SourceInterface $source
    */
    private $source;

    /**
     * @param SourceFactory $factory The source factory, which is useful to create source.
     * @return StorableInterface Chainable
     */
    public function setSourceFactory(SourceFactory $factory)
    {
        $this->sourceFactory = $factory;
        return $this;
    }

    /**
     * @return SourceFactory
     */
    protected function sourceFactory()
    {
        if (!isset($this->sourceFactory)) {
            $this->sourceFactory = new SourceFactory();
        }
        return $this->sourceFactory;
    }

    /**
    * Set the object's source.
    *
    * @param SourceInterface $source The storable object's source.
    * @return StorableInterface Chainable
    */
    public function setSource(SourceInterface $source)
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
            $this->source = $this->createSource();
        }
        return $this->source;
    }

    /**
    * Create the model's source, with the Source Factory.
    *
    * @param mixed $data Optional.
    * @return SourceInterface
    */
    abstract protected function createSource($data = null);

    /**
    * Load an object from the database from its ID.
    *
    * Note that the object should also implement `Charcoal\Model\IndexableInterface`
    * (provide an `id()` and `key()` methods) for this function to work properly.
    *
    * @param mixed $id The identifier to load.
    * @return StorableInterface Chainable
    */
    public function load($id = null)
    {
        if ($id === null) {
            $id = $this->id();
        }
        $this->source()->loadItem($id, $this);
        return $this;
    }

    /**
    * Load an object from the database from its key $key.
    *
    * Note that the object should also implement `Charcoal\Model\IndexableInterface`
    * (provide an `id()` and `key()` methods) for this function to work properly.
    *
    * @param string $key   Key pointing a column's name.
    * @param mixed  $value Value of said column.
    * @return StorableInterface Chainable.
    */
    public function loadFrom($key = null, $value = null)
    {
        $this->source()->loadItemFromKey($key, $value, $this);
        return $this;
    }

    /**
    * Load an object from the database from a custom SQL query.
    *
    * @param string $query The SQL query.
    * @param array  $binds Optional. The SQL query parameters.
    * @return StorableInterface Chainable.
    */
    public function loadFromQuery($query, array $binds = [])
    {
        $this->source()->loadItemFromQuery($query, $binds, $this);
        return $this;
    }

    /**
    * Save an object current state to storage
    *
    * @return boolean
    */
    public function save()
    {
        $pre = $this->preSave();
        if ($pre === false) {
            return false;
        }
        $ret = $this->source()->saveItem($this);
        $this->postSave();
        return $ret;
    }

    /**
    * Update the object in storage to the current object state.
    *
    * @param array $properties If set, only update the properties specified in this array.
    * @return boolean
    */
    public function update(array $properties = null)
    {
        $pre = $this->preUpdate($properties);
        $this->saveProperties($properties);
        if ($pre === false) {
            return false;
        }
        $ret = $this->source()->updateItem($this, $properties);
        $this->postUpdate($properties);
        return $ret;
    }

    /**
    * Delete an object from storage.
    *
    * @return boolean
    */
    public function delete()
    {
        $pre = $this->preDelete();
        if ($pre === false) {
            return false;
        }
        $ret = $this->source()->deleteItem($this);
        $this->postDelete();
        return $ret;
    }

    /**
    * @return boolean
    */
    abstract protected function preSave();

    /**
    * @return boolean
    */
    abstract protected function postSave();

    /**
    * @param string[] $keys Optional. The list of keys to update.
    * @return boolean
    */
    abstract protected function preUpdate(array $keys = null);

    /**
    * @param string[] $keys Optional. The list of keys to update.
    * @return boolean
    */
    abstract protected function postUpdate(array $keys = null);

    /**
    * @return boolean
    */
    abstract protected function preDelete();

    /**
    * @return boolean
    */
    abstract protected function postDelete();
}
