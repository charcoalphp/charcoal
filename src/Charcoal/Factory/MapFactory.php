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
     * The class map array holds available types, in `[$type => $className]` format.
     * @var array $map
     */
    private $map = [];

    /**
     * @param array $data Factory arguments.
     */
    public function __construct(array $data = null)
    {
        parent::__construct($data);

        if ($data['map']) {
            $this->setMap($data['map']);
        }
    }

    /**
     * Add a class name to the available types _map_.
     *
     * @param string $type      The type (class ident).
     * @param string $className The FQN of the class.
     * @throws InvalidArgumentException If the $type parameter is not a striing or the $className class does not exist.
     * @return FactoryInterface Chainable
     */
    public function addClass($type, $className)
    {
        if (!is_string($type)) {
            throw new InvalidArgumentException(
                'Type (class key) must be a string'
            );
        }
        if (!class_exists($className)) {
            throw new InvalidArgumentException(
                sprintf('Class "%s" is not a valid class name.', $className)
            );
        }

        $this->map[$type] = $className;
        return $this;
    }

    /**
     * Add multiple types, in a an array of `type` => `className`.
     *
     * @param array $map The map (key=>classname) to use.
     * @return FactoryInterface Chainable
     */
    public function setMap(array $map)
    {
        // Resets (overwrites) map.
        $this->map = [];
        foreach ($map as $type => $className) {
            $this->addClass($type, $className);
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
     * @return string The resolved class name (FQN).
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
    public function isResolvable($type)
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

        $className = $map[$type];
        return !!class_exists($className);
    }
}
