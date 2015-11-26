<?php

namespace Charcoal\Factory;

// Dependencies from `PHP`
use \Exception;
use \InvalidArgumentException;

// Local namespace dependencies
use \Charcoal\Factory\FactoryInterface;

/**
* Full implementation, as Abstract class, of the FactoryInterface.
*/
abstract class AbstractFactory implements FactoryInterface
{
    /**
    * If a base class is set, then it must be ensured that the
    * @var string $base_class
    */
    private $base_class = '';
    /**
    *
    * @var string $default_class
    */
    private $default_class = '';

    /**
    * Keeps loaded instances in memory, in `[$type => $instance]` format.
    * Used with the `get()` method only.
    * @var array $instances
    */
    private $instances = [];

    /**
    * Create a new instance of a class, by type.
    *
    * Unlike `get()`, this method *always* return a new instance of the requested class.
    *
    * @param string $type The type (class ident)
    * @param array $args The constructor arguments
    * @throws Exception If the
    * @throws InvalidArgumentException if type is not a string or is not an available type
    * @return mixed The instance / object
    */
    public function create($type, array $args = null)
    {
        if (!is_string($type)) {
            throw new InvalidArgumentException(
                __METHOD__.': Type must be a string.'
            );
        }

        if ($this->is_resolvable($type) === false) {
            $default_class = $this->default_class();
            if ($default_class !== '') {
                return new $default_class($args);
            } else {
                throw new InvalidArgumentException(
                    sprintf(
                        __METHOD__.': Type "%s" is not a valid type. (Using default class %s)',
                        $type,
                        $default_class
                    )
                );
            }
        }

        // Create the object from the type's class name.
        $classname = $this->resolve($type);
        $obj = new $classname($args);


        // Ensure base class is respected, if set.
        $base_class = $this->base_class();
        if ($base_class !== '' && !($obj instanceof $base_class)) {
            throw new Exception(
                sprintf(__METHOD__.': Object is not a valid "%s" class', $base_class)
            );
        }

        return $obj;
    }

    /**
    * Get (load or create) an instance of a class, by type.
    *
    * Unlike `create()` (which always call a `new` instance), this function first tries to load / reuse
    * an already created object of this type, from memory.
    *
    * @param string $type The type (class ident)
    * @throws InvalidArgumentException if type is not a string
    * @return mixed The instance / object
    */
    public function get($type, array $args = null)
    {
        if (!is_string($type)) {
            throw new InvalidArgumentException(
                'Type must be a string.'
            );
        }
        if (!isset($this->instances[$type]) || $this->instances[$type] === null) {
            $this->instances[$type] = $this->create($type, $args);
        }
        return $this->instances[$type];
    }

    /**
    * If a base class is set, then it must be ensured that the created objects
    * are `instanceof` this base class.
    *
    * @param string $classname
    * @throws InvalidArgumentException If the class is not a string or is not an existing class / interface.
    * @return FactoryInterface
    */
    public function set_base_class($classname)
    {
        if (!is_string($classname)) {
            throw new InvalidArgumentException(
                'Classname must be a string.'
            );
        }
        $class_exists = (class_exists($classname) || interface_exists($classname));
        if (!$class_exists) {
            throw new InvalidArgumentException(
                sprintf('Can not set "%s" as base class: Invalid class or interface name.', $classname)
            );
        }
        $this->base_class = $classname;
        return $this;
    }

    /**
    * @return string
    */
    public function base_class()
    {
        return $this->base_class;
    }

    /**
    * If a default class is set, then calling `get()` or `create()`
    * an invalid type should return an object of this class instead of throwing an error.
    *
    * @param string $classname
    * @throws InvalidArgumentException
    * @return FactoryInterface
    */
    public function set_default_class($classname)
    {
        if (!is_string($classname)) {
            throw new InvalidArgumentException(
                'Class name must be a string.'
            );
        }
        if ($classname !== '' && !class_exists($classname)) {
            throw new InvalidArgumentException(
                sprintf('Can not set "%s" as defaut class: Invalid class name', $classname)
            );
        }
        $this->default_class = $classname;
        return $this;
    }

    /**
    * @return string
    */
    public function default_class()
    {
        return $this->default_class;
    }



    /**
    * Resolve the class name from "type".
    *
    * @param string $type
    * @throws InvalidArgumentException
    * @return string
    */
    abstract public function resolve($type);

    /**
    * Returns wether a type is resolvable (is valid)
    *
    * @param string $type The type to check
    * @return boolean True if the type is available, false if not
    */
    abstract public function is_resolvable($type);
}
