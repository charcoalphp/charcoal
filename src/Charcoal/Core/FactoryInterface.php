<?php

namespace Charcoal\Core;

/**
*
*/
interface FactoryInterface
{

    /**
    * Set the factory's data / properties.
    * @param array $data
    * @return FactoryInterface Chainable
    */
    public function set_data(array $data);

    /**
    * Set the factory mode.
    * Can be "class_map" or "ident".
    * @param string $mode
    * @return FactoryInterface Chainable
    */
    public function set_factory_mode($mode);
    /**
    * Get the factory mode.
    * Can be "class_map" or "ident".
    * @return string
    */
    public function factory_mode();

    /**
    * If a base class is set, then it must be ensured that the created objects
    * are `instanceof` this base class.
    * @param string $classname
    * @throws InvalidArgumentException
    * @return FactoryInterface
    */
    public function set_base_class($classname);

    /**
    * @return string
    */
    public function base_class();

    /**
    * If a default class is set, then calling `get()` or `create()`
    * an invalid type should return an object of this class instead of throwing an error.
    *
    * @param string $classname
    * @throws InvalidArgumentException
    * @return FactoryInterface
    */
    public function set_default_class($classname);

    /**
    * @return string
    */
    public function default_class();

    /**
    * Create a new instance of a class, by type.
    *
    * @param string $type The type (class ident)
    * @return mixed The instance / object
    */
    public function create($type);

    /**
    * Get an instance of a class, by type.
    *
    * @param string $type The type (class ident)
    * @return mixed
    */
    public function get($type);

    /**
    * Returns wether a type is available
    *
    * @param string $type The type to check
    * @return boolean True if the type is available, false if not
    */
    public function is_type_available($type);

    /**
    * Add multiple types, in a an array of `type` => `classname`.
    *
    * @param array $types
    * @return FactoryInterface Chainable
    */
    public function set_types(array $types);

    /**
    * Add a type to the available types
    *
    * @param string $type  The type (class ident)
    * @param string $class The FQN of the class
    * @return boolean Success / Failure
    */
    public function add_type($type, $class);

    /**
    * Get the map of all types in `[$type => $class]` format
    *
    * @return array
    */
    public function types();
}
