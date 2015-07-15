<?php

namespace Charcoal\Core;

interface FactoryInterface
{
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
    * Get all the currently available types
    *
    * @return array
    */
    public function available_types();

    /**
    * Returns wether a type is available
    *
    * @param string $type The type to check
    * @return boolean True if the type is available, false if not
    */
    public function is_type_available($type);

    /**
    * Add a type to the available types
    *
    * @param string $type  The type (class ident)
    * @param string $class The FQN of the class
    * @return boolean Success / Failure
    */
    public static function add_type($type, $class);

    /**
    * Get the map of all types in `[$type => $class]` format
    *
    * @return array
    */
    public static function types();
}
