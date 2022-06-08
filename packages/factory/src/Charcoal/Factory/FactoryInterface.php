<?php

namespace Charcoal\Factory;

/**
 * Factories instanciate (create) objects.
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
     * A base class name (or interface)
     *
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
}
