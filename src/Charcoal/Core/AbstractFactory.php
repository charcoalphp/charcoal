<?php

namespace Charcoal\Core;

// Dependencies from `PHP`
use \Exception as Exception;
use \InvalidArgumentException as InvalidArgumentException;

// Local namespace dependencies
use \Charcoal\Core\FactoryInterface as FactoryInterface;

/**
* Full implementation, as Abstract class, of the FactoryInterface.
*
* The AbstractFactory is an implementation of the Factory Method / Abstract Fatory Pattern
* in PHP.
*
* ## How to use
* Factories have only one use: create an object.
* The "object type" to create can be dynamic and specified to the 2 fetch methods:
* - `create()`
* - `get()`
*
* ```php
* $factory = new ConcreteFactory;
* // The `create()` method ensures the object is re-created at every call:
* $obj = $factory->create('namespace/vendor/object');
* // The `get()` method returns the created object, or create one if none was set:
* $obj = $factory->get('namespace/vendor/object');
* ```
*
* ## Limiting the types of instanciated objects
* Because of the very dynamic nature of this factory, it is a good practice to limit the type
* of objects that can be created by setting the `base_class` to a base
* class or interface that the instanciated object must extends or implements.
* ```php
* $factory = new ConcreteFactory();
* $factory->set_base_class('\Namespace\Vendor\Foo');
* // This will throw an Exception or return the default object if the resulting object
* // instance does not extends \Namespace\Verndor\Foo
* $obj = $factory->create('bar');
* ```
*
* ## Ensuring an object is always returned.
* By default, calling `get()` or `create()` will throw an exception if the type is not valid.
* (What is considered a valid type depends on the `classname()` method.
*
* It is possible to ensure that an object is always returned by
* setting the *default_class* property to a valid class name.
* ```php
* $factory = new ConcreteFactory();
* $factory->set_default_class('\Namespace\Vendor\Bar');
* // This will return an instance of \Namespace\Vendor\Bar if 'bar' is an invalid type:
* $obj = $factory->create('bar');
* ```
*/
abstract class AbstractFactory implements FactoryInterface
{

    /**
    * Keep latest instances, as singleton copies.
    * @var AbstractFactory $instance
    */
    static protected $instance = [];

    /**
    * If a base class is set, then it must be ensured that the
    * @var string $_base_class
    */
    protected $_base_class = '';
    /**
    *
    * @var string $_default_class
    */
    protected $_default_class = '';

    /**
    * Keeps loaded instances in memory, in `[$type => $instance]` format.
    * @var array $_instances
    */
    protected $_instances = [];

    /**
    * Singleton instance
    *
    * @return FactoryInterface
    */
    final public static function instance()
    {
        $factory_class = get_called_class();

        if (isset(static::$instance[$factory_class]) && static::$instance[$factory_class] !== null) {
            return static::$instance[$factory_class];
        }

        $factory = new $factory_class;
        static::$instance[$factory_class] = $factory;
        return $factory;
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
        $this->_base_class = $classname;
        return $this;
    }

    /**
    * @return string
    */
    public function base_class()
    {
        return $this->_base_class;
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
                'Classname must be a string.'
            );
        }
        if (!class_exists($classname)) {
            throw new InvalidArgumentException(
                sprintf('Can not set "%s" as base class: Invalid class name', $classname)
            );
        }
        $this->_default_class = $classname;
        return $this;
    }

    /**
    * @return string
    */
    public function default_class()
    {
        return $this->_default_class;
    }

    /**
    * Create a new instance of a class, by type.
    *
    * Unlike `get()`, this method *always* return a new instance of the requested class.
    *
    * @param string $type The type (class ident)
    * @throws Exception If the
    * @throws InvalidArgumentException if type is not a string or is not an available type
    * @return mixed The instance / object
    */
    final public function create($type)
    {
        if (!is_string($type)) {
            throw new InvalidArgumentException(
                __METHOD__.': Type must be a string.'
            );
        }

        //
        if (!$this->validate($type)) {
            $default_class = $this->default_class();
            if ($default_class !== '') {
                return new $default_class;
            } else {
                throw new InvalidArgumentException(
                    sprintf(__METHOD__.': Type "%s" is not a valid type.', $type)
                );
            }
        }

        // Create the object from the type's class name.
        $classname = $this->classname($type);
        $obj = new $classname;


        // Ensure base class is respected, if set.
        $base_class = $this->base_class();
        if ($base_class !== '' && !($obj instanceof $base_class)) {
            throw new Exception(
                sprintf(__METHOD__.': Object is not a valid "%s" class', $base_class)
            );
        }

        $this->_instances[$type] = $obj;
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
    final public function get($type)
    {
        if (!is_string($type)) {
            throw new InvalidArgumentException('Type must be a string.');
        }
        if (isset($this->_instances[$type]) && $this->_instances[$type] !== null) {
            return $this->_instances[$type];
        } else {
            return $this->create($type);

        }
    }

    /**
    * Get the class name from "type".
    *
    * @param string $type
    * @throws InvalidArgumentException
    * @return string
    */
    abstract public function classname($type);

    /**
    * Returns wether a type is available
    *
    * @param string $type The type to check
    * @return boolean True if the type is available, false if not
    */
    abstract public function validate($type);
}
