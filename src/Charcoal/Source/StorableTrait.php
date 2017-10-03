<?php

namespace Charcoal\Source;

use Exception;
use InvalidArgumentException;

// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;

// From 'charcoal-core'
use Charcoal\Source\SourceInterface;

/**
 * Full implementation, as trait, of the StorableInterface
 */
trait StorableTrait
{
    /**
     * @var mixed $id The object (unique) identifier
     */
    protected $id;

    /**
     * @var string $key The object key
     */
    protected $key = 'id';

    /**
     * @var FactoryInterface $sourceFactory
     */
    protected $sourceFactory;

    /**
     * @var SourceInterface $source
     */
    private $source;

    /**
     * Set the object's ID. The actual property set depends on `key()`
     *
     * @param mixed $id The object id (identifier / primary key value).
     * @throws InvalidArgumentException If the argument is not scalar.
     * @return StorableInterface Chainable
     */
    public function setId($id)
    {
        if (!is_scalar($id)) {
            throw new InvalidArgumentException(
                sprintf(
                    'ID for "%s" must be a scalar (integer, float, string, or boolean); received %s',
                    get_class($this),
                    (is_object($id) ? get_class($id) : gettype($id))
                )
            );
        }

        $key = $this->key();
        if ($key == 'id') {
            $this->id = $id;
        } else {
            $this[$key] = $id;
        }

        return $this;
    }

    /**
     * Get the object's (unique) ID. The actualy property get depends on `key()`
     *
     * @throws Exception If the set key is invalid.
     * @return mixed
     */
    public function id()
    {
        $key = $this->key();
        if ($key == 'id') {
            return $this->id;
        } else {
            return $this[$key];
        }
    }

    /**
     * Set the key property.
     *
     * @param string $key The object key, or identifier "name".
     * @throws InvalidArgumentException If the argument is not scalar.
     * @return StorableInterface Chainable
     */
    public function setKey($key)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Key must be a string; received %s',
                    (is_object($key) ? get_class($key) : gettype($key))
                )
            );
        }
        // For security reason, only alphanumeric characters (+ underscores) are valid key names.
        // Although SQL can support more, there's really no reason to.
        if (!preg_match_all('/^[A-Za-z0-9_]+$/', $key)) {
            throw new InvalidArgumentException(
                sprintf('Key "%s" is invalid: must be alphanumeric / underscore.', $key)
            );
        }
        $this->key = $key;

        return $this;
    }

    /**
     * Get the key property.
     *
     * @return string
     */
    public function key()
    {
        return $this->key;
    }

    /**
     * @param FactoryInterface $factory The source factory, which is useful to create source.
     * @return StorableInterface Chainable
     */
    protected function setSourceFactory(FactoryInterface $factory)
    {
        $this->sourceFactory = $factory;
        return $this;
    }

    /**
     * @throws Exception If the source factory was not previously set.
     * @return FactoryInterface
     */
    protected function sourceFactory()
    {
        if (!isset($this->sourceFactory)) {
            throw new Exception(
                sprintf('Source factory is not set for "%s"', get_class($this))
            );
        }
        return $this->sourceFactory;
    }

    /**
     * Set the object's source.
     *
     * @param SourceInterface $source The storable object's source.
     * @return StorableInterface Chainable
     * @todo This method needs to go protected.
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
     * @return SourceInterface
     */
    abstract protected function createSource();

    /**
     * Load an object from the database from its ID.
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
            $this->logger->error(sprintf(
                'Can not save object (%s:%s). The preSave() method failed.',
                $this->objType(),
                $this->id()
            ));
            return false;
        }
        $ret = $this->source()->saveItem($this);
        if ($ret === false) {
            $this->logger->error(sprintf(
                'Can not save object (%s:%s). The source\'s saveItem() method failed.',
                $this->objType(),
                $this->id()
            ));
            return false;
        }
        $post = $this->postSave();
        if ($post === false) {
            $this->logger->warning(sprintf(
                'Although the object (%s:%s) was saved, the postSave() method had an error. Expect problems later.',
                $this->objType(),
                $this->id()
            ));
            return false;
        }
        return true;
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
        if ($pre === false) {
            $this->logger->error(sprintf(
                'Can not update object (%s:%s). The preUpdate() method failed.',
                $this->objType(),
                $this->id()
            ));
            return false;
        }
        $ret = $this->source()->updateItem($this, $properties);
        if ($ret === false) {
            $this->logger->error(sprintf(
                'Can not update object (%s:%s). The source\'s updateItem() method failed.',
                $this->objType(),
                $this->id()
            ));
            return false;
        }
        $post = $this->postUpdate($properties);
        if ($post === false) {
            $this->logger->warning(sprintf(
                'Although the object (%s:%s) was updated, the postUpdate() method had an error. Expect problems later.',
                $this->objType(),
                $this->id()
            ));
            return false;
        }
        return true;
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
            $this->logger->error(sprintf(
                'Can not update object (%s:%s). The preUpdate() method failed.',
                $this->objType(),
                $this->id()
            ));
            return false;
        }
        $ret = $this->source()->deleteItem($this);
        if ($ret === false) {
            $this->logger->error(sprintf(
                'Can not delete object (%s:%s). The source\'s deleteItem() method failed.',
                $this->objType(),
                $this->id()
            ));
            return false;
        }
        $del = $this->postDelete();
        if ($del === false) {
            $this->logger->warning(sprintf(
                'Although the object (%s:%s) was deleted, the postDelete() method had an error. Expect problems later.',
                $this->objType(),
                $this->id()
            ));
            return false;
        }
        return true;
    }

    /**
     * @return boolean
     */
    protected function preSave()
    {
        return true;
    }

    /**
     * @return boolean
     */
    protected function postSave()
    {
        return true;
    }

    /**
     * @param string[] $keys Optional. The list of keys to update.
     * @return boolean
     */
    protected function preUpdate(array $keys = null)
    {
        return true;
    }

    /**
     * @param string[] $keys Optional. The list of keys to update.
     * @return boolean
     */
    protected function postUpdate(array $keys = null)
    {
        return true;
    }

    /**
     * @return boolean
     */
    protected function preDelete()
    {
        return true;
    }

    /**
     * @return boolean
     */
    protected function postDelete()
    {
        return true;
    }
}
