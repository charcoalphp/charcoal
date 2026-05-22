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
    public function first(): ?object
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
    public function last(): ?object
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
     */
    public function merge($objs): static
    {
        $objs = $this->asArray($objs);

        foreach ($objs as $obj) {
            if (!$this->isAcceptable($obj)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Must be an array of models, contains %s',
                        (get_debug_type($obj))
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
     */
    public function add($obj): static
    {
        if (!$this->isAcceptable($obj)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Must be a model, received %s',
                    (get_debug_type($obj))
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
     */
    public function has($key): bool
    {
        if ($this->isAcceptable($key)) {
            $key = $this->modelKey($key);
        }

        return array_key_exists((string)$key, $this->objects);
    }

    /**
     * Remove object from collection by primary key.
     *
     * @param  mixed $key The object primary key to remove.
     * @throws InvalidArgumentException If the given key is not acceptable.
     */
    public function remove($key): static
    {
        if ($this->isAcceptable($key)) {
            $key = $this->modelKey($key);
        }

        unset($this->objects[$key]);

        return $this;
    }

    /**
     * Remove all objects from collection.
     */
    public function clear(): static
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
    public function values(): array
    {
        return array_values($this->objects);
    }

    /**
     * Retrieve the primary keys of the objects in the collection.
     *
     * @return array A sequential array of keys.
     */
    public function keys(): array
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
     */
    public function offsetExists($offset): bool
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
    public function offsetGet($offset): mixed
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
     */
    public function offsetSet($offset, $value): void
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
     */
    public function offsetUnset($offset): void
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
        if (is_int($offset) && $offset < 0) {
            $offset = ($this->count() - abs($offset));
        }

        return $offset;
    }

    // Satisfies Countable
    // =============================================================================================
    /**
     * Get number of objects in collection
     *
     * @see    \Countable
     */
    public function count(): int
    {
        return count($this->objects);
    }

    // Satisfies IteratorAggregate
    // =============================================================================================
    /**
     * Retrieve an external iterator.
     *
     * @see    \IteratorAggregate
     */
    public function getIterator(): \ArrayIterator
    {
        return new ArrayIterator($this->objects);
    }

    /**
     * Retrieve a cached iterator.
     *
     * @param  integer $flags Bitmask of flags.
     */
    public function getCachingIterator($flags = CachingIterator::CALL_TOSTRING): \CachingIterator
    {
        return new CachingIterator($this->getIterator(), $flags);
    }

    // Satisfies backwards-compatibility
    // =============================================================================================
    /**
     * Retrieve the array offset from the given key.
     *
     * @param  mixed $key The primary key to retrieve the offset from.
     * @return integer Returns an array offset.
     */
    #[\Deprecated]
    public function pos($key): int|false
    {
        trigger_error('Collection::pos() is deprecated', E_USER_DEPRECATED);

        return array_search($key, array_keys($this->objects));
    }

    /**
     * Alias of {@see self::values()}
     *
     * @todo   Trigger deprecation error.
     * @return object[]
     */
    #[\Deprecated]
    public function objects(): array
    {
        return $this->values();
    }

    /**
     * Alias of {@see self::all()}.
     *
     * @todo   Trigger deprecation error.
     * @return object[]
     */
    #[\Deprecated]
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
     */
    public function isAcceptable($value): bool
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
                    (get_debug_type($obj))
                )
            );
        }

        return $obj->id();
    }

    /**
     * Determine if the collection is empty or not.
     */
    public function isEmpty(): bool
    {
        return empty($this->objects);
    }

    /**
     * Get a base collection instance from this collection.
     *
     * Note: Practical for extended classes.
     */
    public function toBase(): self
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
