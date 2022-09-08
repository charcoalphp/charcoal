<?php

namespace Charcoal\Model;

use Traversable;
use ArrayIterator;
use CachingIterator;
use LogicException;
use InvalidArgumentException;
// From 'charcoal-core'
use Charcoal\Model\CollectionInterface;
use Charcoal\Model\ModelInterface;

/**
 * A Model Collection
 *
 * For iterating instances of {@see ModelInterface}.
 *
 * Used by {@see \Charcoal\Loader\CollectionLoader} for storing results.
 *
 * The collection stores models by {@see \Charcoal\Source\StorableInterface their primary key}.
 * If two objects share the same storable ID but hold disparate data, they are considered
 * to be alike. Adding an object that shares the same ID as an object previously stored in
 * the collection will replace the latter.
 */
class Collection implements CollectionInterface
{
    /**
     * The objects contained in the collection.
     *
     * Stored as a dictionary indexed by each object's primary key.
     * Ensures that each object gets loaded only once by keeping
     * every loaded object in an associative array.
     *
     * @var object[]
     */
    protected $objects = [];

    /**
     * Create a new collection.
     *
     * @param  array|Traversable|null $objs Array of objects to pre-populate this collection.
     * @return void
     */
    public function __construct($objs = null)
    {
        if ($objs) {
            $this->merge($objs);
        }
    }

    /**
     * Retrieve the first object in the collection.
     *
     * @return object|null Returns the first object, or NULL if the collection is empty.
     */
    public function first()
    {
        if (empty($this->objects)) {
            return null;
        }

        return reset($this->objects);
    }

    /**
     * Retrieve the last object in the collection.
     *
     * @return object|null Returns the last object, or NULL if the collection is empty.
     */
    public function last()
    {
        if (empty($this->objects)) {
            return null;
        }

        return end($this->objects);
    }

    // Satisfies CollectionInterface
    // =============================================================================================

    /**
     * Merge the collection with the given objects.
     *
     * @param  array|Traversable $objs Array of objects to append to this collection.
     * @throws InvalidArgumentException If the given array contains an unacceptable value.
     * @return self
     */
    public function merge($objs)
    {
        $objs = $this->asArray($objs);

        foreach ($objs as $obj) {
            if (!$this->isAcceptable($obj)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Must be an array of models, contains %s',
                        (is_object($obj) ? get_class($obj) : gettype($obj))
                    )
                );
            }

            $key = $this->modelKey($obj);
            $this->objects[$key] = $obj;
        }

        return $this;
    }

    /**
     * Add an object to the collection.
     *
     * @param  object $obj An acceptable object.
     * @throws InvalidArgumentException If the given object is not acceptable.
     * @return self
     */
    public function add($obj)
    {
        if (!$this->isAcceptable($obj)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Must be a model, received %s',
                    (is_object($obj) ? get_class($obj) : gettype($obj))
                )
            );
        }

        $key = $this->modelKey($obj);
        $this->objects[$key] = $obj;

        return $this;
    }

    /**
     * Retrieve the object by primary key.
     *
     * @param  mixed $key The primary key.
     * @return object|null Returns the requested object or NULL if not in the collection.
     */
    public function get($key)
    {
        if ($this->isAcceptable($key)) {
            $key = $this->modelKey($key);
        }

        if ($this->has($key)) {
            return $this->objects[$key];
        }

        return null;
    }

    /**
     * Determine if an object exists in the collection by key.
     *
     * @param  mixed $key The primary key to lookup.
     * @return boolean
     */
    public function has($key)
    {
        if ($this->isAcceptable($key)) {
            $key = $this->modelKey($key);
        }

        return array_key_exists($key, $this->objects);
    }

    /**
     * Remove object from collection by primary key.
     *
     * @param  mixed $key The object primary key to remove.
     * @throws InvalidArgumentException If the given key is not acceptable.
     * @return self
     */
    public function remove($key)
    {
        if ($this->isAcceptable($key)) {
            $key = $this->modelKey($key);
        }

        unset($this->objects[$key]);

        return $this;
    }

    /**
     * Remove all objects from collection.
     *
     * @return self
     */
    public function clear()
    {
        $this->objects = [];

        return $this;
    }

    /**
     * Retrieve all objects in collection indexed by primary keys.
     *
     * @return object[] An associative array of objects.
     */
    public function all()
    {
        return $this->objects;
    }

    /**
     * Retrieve all objects in the collection indexed numerically.
     *
     * @return object[] A sequential array of objects.
     */
    public function values()
    {
        return array_values($this->objects);
    }

    /**
     * Retrieve the primary keys of the objects in the collection.
     *
     * @return array A sequential array of keys.
     */
    public function keys()
    {
        return array_keys($this->objects);
    }

    // Satisfies ArrayAccess
    // =============================================================================================

    /**
     * Alias of {@see CollectionInterface::has()}.
     *
     * @see    \ArrayAccess
     * @param  mixed $offset The object primary key or array offset.
     * @return boolean
     */
    public function offsetExists($offset)
    {
        if (is_int($offset)) {
            $offset  = $this->resolveOffset($offset);
            $objects = array_values($this->objects);

            return array_key_exists($offset, $objects);
        }

        return $this->has($offset);
    }

    /**
     * Alias of {@see CollectionInterface::get()}.
     *
     * @see    \ArrayAccess
     * @param  mixed $offset The object primary key or array offset.
     * @return mixed Returns the requested object or NULL if not in the collection.
     */
    public function offsetGet($offset)
    {
        if (is_int($offset)) {
            $offset  = $this->resolveOffset($offset);
            $objects = array_values($this->objects);
            if (isset($objects[$offset])) {
                return $objects[$offset];
            }
        }

        return $this->get($offset);
    }

    /**
     * Alias of {@see CollectionInterface::set()}.
     *
     * @see    \ArrayAccess
     * @param  mixed $offset The object primary key or array offset.
     * @param  mixed $value  The object.
     * @throws LogicException Attempts to assign an offset.
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if ($offset === null) {
            $this->add($value);
        } else {
            throw new LogicException(
                sprintf('Offsets are not accepted on the model collection, received %s.', $offset)
            );
        }
    }

    /**
     * Alias of {@see CollectionInterface::remove()}.
     *
     * @see    \ArrayAccess
     * @param  mixed $offset The object primary key or array offset.
     * @return void
     */
    public function offsetUnset($offset)
    {
        if (is_int($offset)) {
            $offset = $this->resolveOffset($offset);
            $keys   = array_keys($this->objects);
            if (isset($keys[$offset])) {
                $offset = $keys[$offset];
            }
        }

        $this->remove($offset);
    }

    /**
     * Parse the array offset.
     *
     * If offset is non-negative, the sequence will start at that offset in the collection.
     * If offset is negative, the sequence will start that far from the end of the collection.
     *
     * @param  integer $offset The array offset.
     * @return integer Returns the resolved array offset.
     */
    protected function resolveOffset($offset)
    {
        if (is_int($offset)) {
            if ($offset < 0) {
                $offset = ($this->count() - abs($offset));
            }
        }

        return $offset;
    }

    // Satisfies Countable
    // =============================================================================================

    /**
     * Get number of objects in collection
     *
     * @see    \Countable
     * @return integer
     */
    public function count()
    {
        return count($this->objects);
    }

    // Satisfies IteratorAggregate
    // =============================================================================================

    /**
     * Retrieve an external iterator.
     *
     * @see    \IteratorAggregate
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->objects);
    }

    /**
     * Retrieve a cached iterator.
     *
     * @param  integer $flags Bitmask of flags.
     * @return \CachingIterator
     */
    public function getCachingIterator($flags = CachingIterator::CALL_TOSTRING)
    {
        return new CachingIterator($this->getIterator(), $flags);
    }

    // Satisfies backwards-compatibility
    // =============================================================================================

    /**
     * Retrieve the array offset from the given key.
     *
     * @deprecated
     * @param  mixed $key The primary key to retrieve the offset from.
     * @return integer Returns an array offset.
     */
    public function pos($key)
    {
        trigger_error('Collection::pos() is deprecated', E_USER_DEPRECATED);

        return array_search($key, array_keys($this->objects));
    }

    /**
     * Alias of {@see self::values()}
     *
     * @deprecated
     * @todo   Trigger deprecation error.
     * @return object[]
     */
    public function objects()
    {
        return $this->values();
    }

    /**
     * Alias of {@see self::all()}.
     *
     * @deprecated
     * @todo   Trigger deprecation error.
     * @return object[]
     */
    public function map()
    {
        return $this->all();
    }

    // =============================================================================================

    /**
     * Determine if the given value is acceptable for the collection.
     *
     * Note: Practical for specialized collections extending the base collection.
     *
     * @param  mixed $value The value being vetted.
     * @return boolean
     */
    public function isAcceptable($value)
    {
        return ($value instanceof ModelInterface);
    }

    /**
     * Convert a given object into a model identifier.
     *
     * Note: Practical for specialized collections extending the base collection.
     *
     * @param  object $obj An acceptable object.
     * @throws InvalidArgumentException If the given object is not acceptable.
     * @return boolean
     */
    protected function modelKey($obj)
    {
        if (!$this->isAcceptable($obj)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Must be a model, received %s',
                    (is_object($obj) ? get_class($obj) : gettype($obj))
                )
            );
        }

        return $obj->id();
    }

    /**
     * Determine if the collection is empty or not.
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return empty($this->objects);
    }

    /**
     * Get a base collection instance from this collection.
     *
     * Note: Practical for extended classes.
     *
     * @return Collection
     */
    public function toBase()
    {
        return new self($this);
    }

    /**
     * Parse the given value into an array.
     *
     * @link http://php.net/types.array#language.types.array.casting
     *     If an object is converted to an array, the result is an array whose
     *     elements are the object's properties.
     * @param  mixed $value The value being converted.
     * @return array
     */
    protected function asArray($value)
    {
        if (is_array($value)) {
            return $value;
        } elseif ($value instanceof CollectionInterface) {
            return $value->all();
        } elseif ($value instanceof Traversable) {
            return iterator_to_array($value);
        } elseif ($value instanceof ModelInterface) {
            return [ $value ];
        }

        return (array)$value;
    }
}
