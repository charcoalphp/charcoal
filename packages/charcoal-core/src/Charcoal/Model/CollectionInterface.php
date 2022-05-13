<?php

namespace Charcoal\Model;

/**
 * Defines a model collection.
 */
interface CollectionInterface extends \ArrayAccess, \Countable, \IteratorAggregate
{
    /**
     * Merge the collection with the given objects.
     *
     * @param  array|\Traversable $objs Array of objects to append to this collection.
     * @return CollectionInterface
     */
    public function merge($objs);

    /**
     * Add an object to the collection.
     *
     * @param  object $obj An acceptable object.
     * @return CollectionInterface
     */
    public function add($obj);

    /**
     * Retrieve the object by primary key.
     *
     * @param  mixed $key The primary key.
     * @return object|null The object or NULL if not in the collection.
     */
    public function get($key);

    /**
     * Determine if an object exists in the collection by key.
     *
     * @param  string $key The primary key to lookup.
     * @return boolean
     */
    public function has($key);

    /**
     * Remove object from collection by primary key.
     *
     * @param  mixed $key The object primary key to remove.
     * @return CollectionInterface
     */
    public function remove($key);

    /**
     * Remove all objects from collection.
     *
     * @return CollectionInterface
     */
    public function clear();

    /**
     * Retrieve all objects in collection indexed by primary keys.
     *
     * @return object[] An associative array of objects.
     */
    public function all();

    /**
     * Retrieve all objects in the collection indexed numerically.
     *
     * @return object[] A sequential array of objects.
     */
    public function values();

    /**
     * Retrieve the primary keys of the objects in the collection.
     *
     * @return array A sequential array of keys.
     */
    public function keys();
}
