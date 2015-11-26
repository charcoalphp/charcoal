<?php

namespace Charcoal\Factory;

// Dependencies from `PHP`
use \InvalidArgumentException;

// Local namespace dependencies
use \Charcoal\Factory\AbstractFactory;

/**
 * The map Factory resolves the **class name** from an associative array with the **type** key.
 *
 */
class MapFactory extends AbstractFactory
{
    /**
     * The class map array holds available types, in `[$type => $class_name]` format.
     * @var array $map
     */
    private $map = [];

    /**
     * Add a class name to the available types _map_.
     *
     * @param string $type       The type (class ident).
     * @param string $class_name The FQN of the class.
     * @throws InvalidArgumentException If the $type parameter is not a striing or the $class_name class does not exist.
     * @return FactoryInterface Chainable
     */
    public function add_class($type, $class_name)
    {
        if (!is_string($type)) {
            throw new InvalidArgumentException(
                'Type (class key) must be a string'
            );
        }
        if (!class_exists($class_name)) {
            throw new InvalidArgumentException(
                sprintf('Class "%s" is not a valid class name.', $class_name)
            );
        }

        $this->map[$type] = $class_name;
        return $this;
    }

    /**
     * Add multiple types, in a an array of `type` => `class_name`.
     *
     * @param array $map The map (key=>classname) to use.
     * @return FactoryInterface Chainable
     */
    public function set_map(array $map)
    {
        // Resets (overwrites) map.
        $this->map = [];
        foreach ($map as $type => $class_name) {
            $this->add_class($type, $class_name);
        }
        return $this;
    }

    /**
     * Get the map of all types in `[$type => $class]` format.
     *
     * @return array
     */
    public function map()
    {
        return $this->map;
    }

    /**
     * The "Map Factory" implements `AbstractFactory`'s `resolve()` abstract method
     * by fetching the class ident from the `map` member array.
     *
     * If the object's `type` is not defined in the class map, an exception will be thrown.
     *
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

        $map = $this->map();
        if (!isset($map[$type])) {
            throw new InvalidArgumentException(
                'Invalid type (not defined in class map)'
            );
        }
        return $map[$type];
    }

    /**
     * The "Map Factory" implements `AbstractFactory`'s `is_resolvable()` abstract method
     * by ensuring the class ident is defined in the class map and is a validd class.
     *
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

        $map = $this->map();
        if (!isset($map[$type])) {
            return false;
        }
        
        $class_name = $map[$type];
        return !!class_exists($class_name);
    }
}
