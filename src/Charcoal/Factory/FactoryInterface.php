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
     * @return string
     */
    public function baseClass();

    /**
     * @return string
     */
    public function defaultClass();

    /**
     * @return array
     */
    public function arguments();

    /**
     * @return callable|null
     */
    public function callback();

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
