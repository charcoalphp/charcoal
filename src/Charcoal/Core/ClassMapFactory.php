<?php

namespace Charcoal\Core;

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
    protected $class_map = [];

    /**
    * Set the factory's data / properties
    *
    * @param array $data
    * @return
    */
    public function set_data(array $data)
    {
        parent::set_data($data);
        if (isset($data['class_map']) && $data['class_map'] !== null) {
            $this->set_class_map($data['types']);
        }
        return $this;
    }

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
    * @param string $type
    * @throws InvalidArgumentException
    * @return string
    */
    public function classname($type)
    {
        $types = $this->class_map();
        if (!isset($types[$type])) {
            throw new InvalidArgumentException('Invalid type');
        }
        return $types[$type];
    }

    /**
    * @param string $type
    * @return boolean
    */
    public function validate($type)
    {
        $types = $this->class_map();
        if (!in_array($type, array_keys($types))) {
            return false;
        } else {
            $class_name = $types[$type];
            return class_exists($class_name);
        }
    }
}
