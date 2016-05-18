<?php

namespace Charcoal\Factory;

/**
 *
 */
interface FactoryInterface
{
    /**
     * Create a new instance of a class, by type.
     *
     * @param string   $type The type (class ident).
     * @param array    $args Constructor arguments.
     * @param callable $cb   Object callback.
     * @return mixed The instance / object
     */
    public function create($type, array $args = null, callable $cb = null);

    /**
     * Get an instance of a class, by type.
     *
     * @param string $type The type (class ident).
     * @param array  $args Constructor arguments.
     * @return mixed
     */
    public function get($type, array $args = null);

    /**
     * If a base class is set, then it must be ensured that the created objects
     * are `instanceof` this base class.
     *
     * @param string $classname The FQN of the class to set as base class.
     * @return FactoryInterface
     */
    public function setBaseClass($classname);

    /**
     * @return string
     */
    public function baseClass();

    /**
     * If a default class is set, then calling `get()` or `create()`
     * an invalid type should return an object of this class instead of throwing an error.
     *
     * @param string $classname The FQN of the class to set as default class.
     * @return FactoryInterface
     */
    public function setDefaultClass($classname);

    /**
     * @return string
     */
    public function defaultClass();

    /**
     * @param array $arguments The constructor arguments to be passed to the created object's initialization.
     * @return AbstractFactory Chainable
     */
    public function setArguments(array $arguments);

    /**
     * @return array
     */
    public function arguments();

    /**
     * Get the class name (FQN) from "type".
     *
     * @param string $type The "type" of object to resolve (the object ident).
     * @return string The resolved class name (FQN).
     */
    public function resolve($type);

    /**
     * Returns wether a type is available (is valid).
     *
     * @param string $type The "type" of object to resolve (the object ident).
     * @return boolean True if the type is available, false if not.
     */
    public function isResolvable($type);
}
