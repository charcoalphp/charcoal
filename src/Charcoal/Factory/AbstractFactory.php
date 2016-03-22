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
     * @var array $resolved
     */
    static protected $resolved = [];

    /**
     * If a base class is set, then it must be ensured that the
     * @var string $baseClass
     */
    private $baseClass = '';
    /**
     *
     * @var string $defaultClass
     */
    private $defaultClass = '';

    /**
     * @var array $arguments
     */
    private $arguments = null;

    /**
     * @var callable $callback
     */
    private $callback = null;

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
     * ## Object callback
     * It is possible to pass a callback method that will be executed upon object instanciation.
     * The callable should have a signature: `function($obj);` where $obj is the newly created object.
     *
     *
     * @param string   $type The type (class ident).
     * @param array    $args Optional. The constructor arguments. Leave blank to use `$arguments` member.
     * @param callable $cb   Optional. Object callback, called at creation. Leave blank to use `$callback` member.
     * @throws Exception If the base class is set and  the resulting instance is not of the base class.
     * @throws InvalidArgumentException If type argument is not a string or is not an available type.
     * @return mixed The instance / object
     */
    final public function create($type, array $args = null, callable $cb = null)
    {
        if (!is_string($type)) {
            throw new InvalidArgumentException(
                sprintf(
                    '%s: Type must be a string.',
                    get_called_class()
                )
            );
        }

        if (!isset($args)) {
            $args = $this->arguments();
        }

        if (!isset($cb)) {
            $cb = $this->callback();
        }

        if (isset(self::$resolved[$type])) {
            $classname = self::$resolved[$type];
        } else {
            if ($this->isResolvable($type) === false) {
                $defaultClass = $this->defaultClass();
                if ($defaultClass !== '') {
                    $obj = new $defaultClass($args);
                    if (isset($cb)) {
                        $cb($obj);
                    }
                    return $obj;
                } else {
                    throw new InvalidArgumentException(
                        sprintf(
                            '%1$s: Type "%2$s" is not a valid type. (Using default class "%3$s")',
                            get_called_class(),
                            $type,
                            $defaultClass
                        )
                    );
                }
            }

            // Create the object from the type's class name.
            $classname = $this->resolve($type);
            self::$resolved[$type] = $classname;
        }

        $obj = new $classname($args);


        // Ensure base class is respected, if set.
        $baseClass = $this->baseClass();
        if ($baseClass !== '' && !($obj instanceof $baseClass)) {
            throw new Exception(
                sprintf(
                    '%1$s: Object is not a valid "%2$s" class',
                    get_called_class(),
                    $baseClass
                )
            );
        }

        if (isset($cb)) {
            $cb($obj);
        }

        return $obj;
    }

    /**
     * Get (load or create) an instance of a class, by type.
     *
     * Unlike `create()` (which always call a `new` instance), this function first tries to load / reuse
     * an already created object of this type, from memory.
     *
     * @param string $type The type (class ident).
     * @param array  $args The constructor arguments (optional).
     * @throws InvalidArgumentException If type argument is not a string.
     * @return mixed The instance / object
     */
    final public function get($type, array $args = null)
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
     * @param string $type The FQN of the class, or "type" of object, to set as base class.
     * @throws InvalidArgumentException If the class is not a string or is not an existing class / interface.
     * @return FactoryInterface
     */
    public function setBaseClass($type)
    {
        if (!is_string($type) || empty($type)) {
            throw new InvalidArgumentException(
                'Class name or type must be a non-empty string.'
            );
        }

        $exists = (class_exists($type) || interface_exists($type));
        if ($exists) {
            $classname = $type;
        } else {
            $classname = $this->resolve($type);

            $exists = (class_exists($classname) || interface_exists($classname));
            if (!$exists) {
                throw new InvalidArgumentException(
                    sprintf('Can not set "%s" as base class: Invalid class or interface name.', $classname)
                );
            }
        }

        $this->baseClass = $classname;

        return $this;
    }

    /**
     * @return string The FQN of the base class
     */
    public function baseClass()
    {
        return $this->baseClass;
    }

    /**
     * If a default class is set, then calling `get()` or `create()` an invalid type
     * should return an object of this class instead of throwing an error.
     *
     * @param string $type The FQN of the class, or "type" of object, to set as default class.
     * @throws InvalidArgumentException If the class name is not a string or not a valid class.
     * @return FactoryInterface
     */
    public function setDefaultClass($type)
    {
        if (!is_string($type) || empty($type)) {
            throw new InvalidArgumentException(
                'Class name or type must be a non-empty string.'
            );
        }

        if (class_exists($type)) {
            $classname = $type;
        } else {
            $classname = $this->resolve($type);

            if (!class_exists($classname)) {
                throw new InvalidArgumentException(
                    sprintf('Can not set "%s" as defaut class: Invalid class name.', $classname)
                );
            }
        }

        $this->defaultClass = $classname;

        return $this;
    }

    /**
     * @return string The FQN of the default class
     */
    public function defaultClass()
    {
        return $this->defaultClass;
    }

    /**
     * @param array $arguments The constructor arguments to be passed to the created object's initialization.
     * @return AbstractFactory Chainable
     */
    public function setArguments(array $arguments)
    {
        $this->arguments = $arguments;
        return $this;
    }

    /**
     * @return array
     */
    public function arguments()
    {
        return $this->arguments;
    }

    /**
     * @param callable $callback The object callback.
     * @return AbstractFatory Chainable
     */
    public function setCallback(callable $callback)
    {
        $this->callback = $callback;
        return $this;
    }

    /**
     * @return callable|null
     */
    public function callback()
    {
        return $this->callback;
    }

    /**
     * Resolve the class name from "type".
     *
     * @param string $type The "type" of object to resolve (the object ident).
     * @return string
     */
    abstract public function resolve($type);

    /**
     * Returns wether a type is resolvable (is valid).
     *
     * @param string $type The "type" of object to resolve (the object ident).
     * @return boolean True if the type is available, false if not
     */
    abstract public function isResolvable($type);
}
