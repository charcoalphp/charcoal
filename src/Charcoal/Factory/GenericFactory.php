<?php

namespace Charcoal\Factory;

use \InvalidArgumentException;

// Local namespace dependencies
use \Charcoal\Factory\AbstractFactory;

/**
 * The Generic Factory resolves the **class name** from an exact full qualifed name as **type**.
 */
class GenericFactory extends AbstractFactory
{
    /**
     * The Generic factory resolves the class name from an exact FQN.
     *
     * @param string $type The "type" of object to resolve (the object ident).
     * @throws InvalidArgumentException If the type parameter is not a string.
     * @return string The resolved class name (FQN).
     */
    public function resolve($type)
    {
        if (!is_string($type)) {
            throw new InvalidArgumentException(
                'Can not resolve class ident: type must be a string'
            );
        }
        return $type;
    }

    /**
     * Wether a `type` is resolvable. The Generic Factory simply checks if the _FQN_ `type` class exists.
     *
     * @param string $type The "type" of object to resolve (the object ident).
     * @throws InvalidArgumentException If the type parameter is not a string.
     * @return boolean
     */
    public function isResolvable($type)
    {
        if (!is_string($type)) {
            throw new InvalidArgumentException(
                'Can not check resolvable: type must be a string'
            );
        }
        return !!class_exists($type);
    }
}
