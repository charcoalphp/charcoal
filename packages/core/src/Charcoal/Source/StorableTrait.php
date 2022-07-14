<?php

namespace Charcoal\Source;

use RuntimeException;
use InvalidArgumentException;
// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;

/**
 * Provides an object with storage interaction.
 *
 * Full implementation, as trait, of the {@see StorableInterface}
 *
 * @property-read \Psr\Log\LoggerInterface $logger The PSR-3 logger instance.
 */
trait StorableTrait
{
    /**
     * The object's unique identifier.
     *
     * @var mixed
     */
    protected $id;

    /**
     * The object's property for uniquely identifying it in storage.
     *
     * @var string
     */
    protected $key = 'id';

    /**
     * The datasource repository factory.
     *
     * @var FactoryInterface
     */
    protected $sourceFactory;

    /**
     * The object's datasource repository.
     *
     * @var SourceInterface
     */
    private $source;

    /**
     * Set the object's unique ID.
     *
     * The actual property set depends on `key()`.
     *
     * @param  mixed $id The object's ID.
     * @throws InvalidArgumentException If the argument is not scalar.
     * @return self
     */
    public function setId($id)
    {
        if (!is_scalar($id)) {
            throw new InvalidArgumentException(sprintf(
                'ID for "%s" must be a scalar (integer, float, string, or boolean); received %s',
                get_class($this),
                (is_object($id) ? get_class($id) : gettype($id))
            ));
        }

        $key = $this->key();
        if ($key === 'id') {
            $this->id = $id;
        } else {
            $this[$key] = $id;
        }

        return $this;
    }

    /**
     * Get the object's unique ID.
     *
     * The actualy property get depends on `key()`.
     *
     * @return mixed
     */
    public function id()
    {
        $key = $this->key();
        if ($key === 'id') {
            return $this->id;
        } else {
            return $this[$key];
        }
    }

    /**
     * Allow ID to also be accessed with ArrayAccess.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id();
    }

    /**
     * Set the primary property key.
     *
     * For uniquely identifying this object in storage.
     *
     * Note: For security reason, only alphanumeric characters (and underscores)
     * are valid key names. Although SQL can support more, there's really no reason to.
     *
     * @param  string $key The object's primary key.
     * @throws InvalidArgumentException If the argument is not scalar.
     * @return self
     */
    public function setKey($key)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException(sprintf(
                'Key must be a string; received %s',
                (is_object($key) ? get_class($key) : gettype($key))
            ));
        }

        if (!preg_match_all('/^[A-Za-z0-9_]+$/', $key)) {
            throw new InvalidArgumentException(
                sprintf('Key "%s" is invalid: must be alphanumeric / underscore.', $key)
            );
        }

        $this->key = $key;
        return $this;
    }

    /**
     * Get the primary property key.
     *
     * @return string
     */
    public function key()
    {
        return $this->key;
    }

    /**
     * Set the object's datasource repository.
     *
     * @todo   This method needs to be protected.
     * @param  SourceInterface $source The storable object's source.
     * @return self
     */
    public function setSource(SourceInterface $source)
    {
        $this->source = $source;
        return $this;
    }

    /**
     * Get the object's datasource repository.
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
     * Create a datasource repository for the model
     * (using the {@see self::$sourceFactory source factory}).
     *
     * @return SourceInterface A new repository.
     */
    abstract protected function createSource();

    /**
     * Load an object from the database from its ID.
     *
     * @param  mixed $id The identifier to load.
     * @return self
     */
    final public function load($id = null)
    {
        if ($id === null) {
            $id = $this->id();
        }
        $this->source()->loadItem($id, $this);
        return $this;
    }

    /**
     * Load an object from the repository from its key $key.
     *
     * @param  string $key   Key pointing a column's name.
     * @param  mixed  $value Value of said column.
     * @return self
     */
    final public function loadFrom($key = null, $value = null)
    {
        $this->source()->loadItemFromKey($key, $value, $this);
        return $this;
    }

    /**
     * Load an object from the repository from a custom SQL query.
     *
     * @param  string $query The SQL query.
     * @param  array  $binds Optional. The SQL query parameters.
     * @return self
     */
    final public function loadFromQuery($query, array $binds = [])
    {
        $this->source()->loadItemFromQuery($query, $binds, $this);
        return $this;
    }

    /**
     * Insert the object's current state in storage.
     *
     * @return boolean TRUE on success.
     */
    final public function save()
    {
        $pre = $this->preSave();
        if ($pre === false) {
            $this->logger->error(sprintf(
                'Can not save object "%s:%s"; cancelled by %s::preSave()',
                $this->objType(),
                $this->id(),
                get_called_class()
            ));
            return false;
        }

        $ret = $this->source()->saveItem($this);
        if ($ret === false) {
            $this->logger->error(sprintf(
                'Can not save object "%s:%s"; repository failed for %s',
                $this->objType(),
                $this->id(),
                get_called_class()
            ));
            return false;
        } else {
            $this->setId($ret);
        }

        $post = $this->postSave();
        if ($post === false) {
            $this->logger->error(sprintf(
                'Saved object "%s:%s" but %s::postSave() failed',
                $this->objType(),
                $this->id(),
                get_called_class()
            ));
            return false;
        }

        return true;
    }

    /**
     * Update the object in storage with the current state.
     *
     * @param  string[] $keys If provided, only update the properties specified.
     * @return boolean TRUE on success.
     */
    final public function update(array $keys = null)
    {
        $pre = $this->preUpdate($keys);
        if ($pre === false) {
            $this->logger->error(sprintf(
                'Can not update object "%s:%s"; cancelled by %s::preUpdate()',
                $this->objType(),
                $this->id(),
                get_called_class()
            ));
            return false;
        }

        $ret = $this->source()->updateItem($this, $keys);
        if ($ret === false) {
            $this->logger->error(sprintf(
                'Can not update object "%s:%s"; repository failed for %s',
                $this->objType(),
                $this->id(),
                get_called_class()
            ));
            return false;
        }

        $post = $this->postUpdate($keys);
        if ($post === false) {
            $this->logger->warning(sprintf(
                'Updated object "%s:%s" but %s::postUpdate() failed',
                $this->objType(),
                $this->id(),
                get_called_class()
            ));
            return false;
        }

        return true;
    }

    /**
     * Delete an object from storage.
     *
     * @return boolean TRUE on success.
     */
    final public function delete()
    {
        $pre = $this->preDelete();
        if ($pre === false) {
            $this->logger->error(sprintf(
                'Can not delete object "%s:%s"; cancelled by %s::preDelete()',
                $this->objType(),
                $this->id(),
                get_called_class()
            ));
            return false;
        }

        $ret = $this->source()->deleteItem($this);
        if ($ret === false) {
            $this->logger->error(sprintf(
                'Can not delete object "%s:%s"; repository failed for %s',
                $this->objType(),
                $this->id(),
                get_called_class()
            ));
            return false;
        }

        $del = $this->postDelete();
        if ($del === false) {
            $this->logger->warning(sprintf(
                'Deleted object "%s:%s" but %s::postDelete() failed',
                $this->objType(),
                $this->id(),
                get_called_class()
            ));
            return false;
        }

        return true;
    }

    /**
     * Set the datasource repository factory.
     *
     * @param  FactoryInterface $factory The source factory.
     * @return self
     */
    protected function setSourceFactory(FactoryInterface $factory)
    {
        $this->sourceFactory = $factory;
        return $this;
    }

    /**
     * Get the datasource repository factory.
     *
     * @throws RuntimeException If the source factory was not previously set.
     * @return FactoryInterface
     */
    protected function sourceFactory()
    {
        if (!isset($this->sourceFactory)) {
            throw new RuntimeException(
                sprintf('Source factory is not set for "%s"', get_class($this))
            );
        }
        return $this->sourceFactory;
    }

    /**
     * Event called before {@see self::save() creating} the object.
     *
     * @return boolean TRUE to proceed with creation; FALSE to stop creation.
     */
    protected function preSave()
    {
        return true;
    }

    /**
     * Event called after {@see self::save() creating} the object.
     *
     * @return boolean TRUE to indicate object was created.
     */
    protected function postSave()
    {
        return true;
    }

    /**
     * Event called before {@see self::update() updating} the object.
     *
     * @param  string[] $keys Optional list of properties to update.
     * @return boolean TRUE to proceed with update; FALSE to stop update.
     */
    protected function preUpdate(array $keys = null)
    {
        return true;
    }

    /**
     * Event called after {@see self::update() updating} the object.
     *
     * @param  string[] $keys Optional list of properties to update.
     * @return boolean TRUE to indicate object was updated.
     */
    protected function postUpdate(array $keys = null)
    {
        return true;
    }

    /**
     * Event called before {@see self::delete() deleting} the object.
     *
     * @return boolean TRUE to proceed with deletion; FALSE to stop deletion.
     */
    protected function preDelete()
    {
        return true;
    }

    /**
     * Event called after {@see self::delete() deleting} the object.
     *
     * @return boolean TRUE to indicate object was deleted.
     */
    protected function postDelete()
    {
        return true;
    }
}
