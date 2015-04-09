<?php

namespace Charcoal\Core;

abstract class AbstractFactory implements FactoryInterface
{
    /**
    * Keeps loaded instances in memory, in `[$type=>$instance]` format.
    * @var array $_instances
    */
    protected $_instances = [];

    /**
    * Available types, in `[$type=>$classname]` format.
    * @var array $_types
    */
    static protected $_types = [];

    /**
    * Create a new instance of a class, by type.
    *
    * @param string $type The type (class ident)
    * @throws \InvalidArgumentException if type is not a string or is not an available type
    * @return mixed The instance / object
    */
    public function create($type)
    {
        if(!is_string($type)) {
            throw new \InvalidArgumentException('Type must be a string');
        }
        if(!$this->is_type_available($type)) {
            throw new \InvalidArgumentException(sprintf('Type "%s" is not a valid type', $type));
        }
        $types = static::types();
        $class = $types[$type];
        return new $class;
    }

    /**
    * Get (load or create) an instance of a class, by type.
    *
    * Unlike create (which always call a `new` instance), this function first tries to load / reuse
    * an already created object of this type, from memory.
    *
    * @param string $type The type (class ident)
    * @throws \InvalidArgumentException if type is not a string
    * @return mixed The instance / object
    */
    public function get($type)
    {
        if(!is_string($type)) {
            throw new \InvalidArgumentException('Type must be a string');
        }
        if(isset($this->_instances[$type]) && $this->_instances[$type] !== null) {
            return $this->_instances[$type];
        }
        else {
            $this->create($type);
        }
    }



    /**
    * Get all the currently available types
    *
    * @return array
    */
    public function available_types()
    {
        return array_keys(static::types());
    }

    /**
    * Returns wether a type is available
    *
    * @param string $type The type to check
    * @return boolean True if the type is available, false if not
    */
    public function is_type_available($type)
    {
        $types = static::types();
        if(!in_array($type, array_keys($types))) {
            return false;
        }
        else {
            $class = $types[$type];
            if(!class_exists($class)) {
                return false;
            }
            else {
                return true;
            }
        }
    }

    /**
    * Add a type to the available types
    *
    * @param string $type The type (class ident)
    * @param string $class The FQN of the class
    * @return boolean Success / Failure
    */
    static public function add_type($type, $class)
    {
        if(!class_exists($class)) {
            return false;
        }
        else {
            static::$_types[$type] = $class;
            return true;
        }
    }

    /**
    * Get the map of all types in `[$type => $class]` format
    *
    * @return array
    */
    static public function types()
    {
        return static::$_types;
    }
}
