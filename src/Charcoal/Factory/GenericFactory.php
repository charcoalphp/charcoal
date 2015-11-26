<?php

namespace Charcoal\Factory;

use \InvalidArgumentException;

// Local namespace dependencies
use \Charcoal\Factory\AbstractFactory;

/**
 *
 */
class GenericFactory extends AbstractFactory
{
    /**
     * @param string $type The "type" of object to resolve (the object ident).
     * @throws InvalidArgumentException If the type parameter is not a string.
     * @return string
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
     * @param string $type The "type" of object to resolve (the object ident).
     * @throws InvalidArgumentException If the type parameter is not a string.
     * @return boolean
     */
    public function is_resolvable($type)
    {
        if (!is_string($type)) {
            throw new InvalidArgumentException(
                'Can not check resolvable: type must be a string'
            );
        }
        return !!class_exists($type);
    }
}
