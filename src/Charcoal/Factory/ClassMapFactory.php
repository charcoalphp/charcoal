<?php

namespace Charcoal\Factory;

// Dependencies from `PHP`
use \InvalidArgumentException;

/**
* Class Map Factory
*
*/
class ClassMapFactory extends AbstractFactory
{
    /**
    * Available types, in `[$type => $classname]` format.
    * @var array $class_map
    */
    private $class_map = [];


    /**
    * Add multiple types, in a an array of `type` => `classname`.
    *
    * @param array $types
    * @return AbstractFactory Chainable
    */
    public function set_class_map(array $types)
    {
        $this->class_map = [];
        foreach ($types as $type => $classname) {
            $this->add_class($type, $classname);
        }
        return $this;
    }

    /**
    * Add a type to the available types
    *
    * @param string $type  The type (class ident)
    * @param string $classname The FQN of the class
    * @throws InvalidArgumentException
    * @return AbstractFactory Chainable
    */
    public function add_class($type, $classname)
    {
        if (!class_exists($classname)) {
            throw new InvalidArgumentException(
                sprintf('Class "%s" is not a valid class name.', $classname)
            );
        }

        $this->class_map[$type] = $classname;
        return $this;
    }

    /**
    * Get the map of all types in `[$type => $class]` format
    *
    * @return array
    */
    public function class_map()
    {
        return $this->class_map;
    }

    /**
    * The "Class Map Factory" implements `AbstractFactory`'s `classname()` abstract method
    * by fetching the class ident from the `class_map` member array.
    *
    * If the object's `class_ident` is not defined in the class map, an exception will be thrown.
    *
    * @param string $type
    * @throws InvalidArgumentException
    * @return string
    */
    public function classname($class_ident)
    {
        $class_map = $this->class_map();
        if (!isset($class_map[$class_ident])) {
            throw new InvalidArgumentException(
                'Invalid type (not defined in class map)'
            );
        }
        return $class_map[$class_ident];
    }

    /**
    * The "Class Map Factory" implements `AbstractFactory`'s `validate()` abstract method
    * by ensuring the class ident is defined in the class map and is a validd class.
    *
    * @param string $type
    * @return boolean
    */
    public function validate($type)
    {
        $types = $this->class_map();
        if (!in_array($type, array_keys($types))) {
            return false;
        }
        
        $class_name = $types[$type];
        return class_exists($class_name);
    }
}
