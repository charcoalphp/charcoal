<?php

namespace Charcoal\Factory;

use Exception;
use InvalidArgumentException;
use ReflectionClass;

/**
 * Full implementation, as Abstract class, of the FactoryInterface.
 *
 * ## Class dependencies:
 *
 * | Name               | Type       | Description                            |
 * | ------------------ | ---------- | -------------------------------------- |
 * | `base_class`       | _string_   | Optional. A base class (or interface) to ensure a type of object.
 * | `default_class`    | _string_   | Optional. A default class, as fallback when the requested object is not resolvable.
 * | `arguments`        | _array_    | Optional. Constructor arguments that will be passed along to created instances.
 * | `callback`         | _Callable_ | Optional. A callback function that will be called upon object creation.
 * | `resolver`         | _Callable_ | Optional. A class resolver. If none is provided, a default will be used.
 * | `resolver_options` | _array_    | Optional. Resolver options (prefix, suffix, capitals and replacements). This is ignored / unused if `resolver` is provided.
 *
 */
abstract class AbstractFactory implements FactoryInterface
{
    /**
     * @var array $resolved
     */
    protected $resolved = [];

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
    private $arguments;

    /**
     * @var callable $callback
     */
    private $callback;

    /**
     * Keeps loaded instances in memory, in `[$type => $instance]` format.
     * Used with the `get()` method only.
     * @var array $instances
     */
    private $instances = [];

    /**
     * @var callable $resolver
     */
    private $resolver;

    /**
     * The class map array holds available types, in `[$type => $className]` format.
     * @var string[] $map
     */
    private $map = [];

    /**
     * @param array $data Constructor dependencies.
     */
    public function __construct(array $data = null)
    {
        if (isset($data['base_class'])) {
            $this->setBaseClass($data['base_class']);
        }

        if (isset($data['default_class'])) {
            $this->setDefaultClass($data['default_class']);
        }

        if (isset($data['arguments'])) {
            $this->setArguments($data['arguments']);
        }

        if (isset($data['callback'])) {
            $this->setCallback($data['callback']);
        }

        if (!isset($data['resolver'])) {
            $opts = isset($data['resolver_options']) ? $data['resolver_options'] : null;
            $data['resolver'] = new GenericResolver($opts);
        }

        $this->setResolver($data['resolver']);

        if (isset($data['map'])) {
            $this->setMap($data['map']);
        }
    }

    /**
     * Create a new instance of a class, by type.
     *
     * Unlike `get()`, this method *always* return a new instance of the requested class.
     *
     * ## Object callback
     * It is possible to pass a callback method that will be executed upon object instanciation.
     * The callable should have a signature: `function($obj);` where $obj is the newly created object.
     *
     * @param  string   $type The type (class ident).
     * @param  array    $args Optional. Constructor arguments
     *     (will override the arguments set on the class from constructor).
     * @param  callable $cb   Optional. Object callback, called at creation.
     *     Will run in addition to the default callback, if any.
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

        $pool = get_called_class();
        if (isset($this->resolved[$pool][$type])) {
            $className = $this->resolved[$pool][$type];
        } else {
            if ($this->isResolvable($type) === false) {
                $defaultClass = $this->defaultClass();
                if ($defaultClass !== '') {
                    $obj = $this->createClass($defaultClass, $args);
                    $this->runCallbacks($obj, $cb);
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
            $className = $this->resolve($type);
            $this->resolved[$pool][$type] = $className;
        }

        $obj = $this->createClass($className, $args);

        // Ensure base class is respected, if set.
        $baseClass = $this->baseClass();
        if ($baseClass !== '' && !($obj instanceof $baseClass)) {
            throw new Exception(
                sprintf(
                    '%1$s: Class "%2$s" must be an instance of "%3$s"',
                    get_called_class(),
                    $className,
                    $baseClass
                )
            );
        }

        $this->runCallbacks($obj, $cb);

        return $obj;
    }

    /**
     * Get (load or create) an instance of a class, by type.
     *
     * Unlike `create()` (which always call a `new` instance),
     * this function first tries to load / reuse
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
     * @return self
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
            $className = $type;
        } else {
            $className = $this->resolve($type);

            $exists = (class_exists($className) || interface_exists($className));
            if (!$exists) {
                throw new InvalidArgumentException(
                    sprintf('Can not set "%s" as base class: Invalid class or interface name.', $className)
                );
            }
        }

        $this->baseClass = $className;

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
     * @return self
     */
    public function setDefaultClass($type)
    {
        if (!is_string($type) || empty($type)) {
            throw new InvalidArgumentException(
                'Class name or type must be a non-empty string.'
            );
        }

        if (class_exists($type)) {
            $className = $type;
        } else {
            $className = $this->resolve($type);

            if (!class_exists($className)) {
                throw new InvalidArgumentException(
                    sprintf('Can not set "%s" as defaut class: Invalid class name.', $className)
                );
            }
        }

        $this->defaultClass = $className;

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
     * @return self
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
     * @return self
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
     * The Generic factory resolves the class name from an exact FQN.
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
        if (isset($map[$type])) {
            $type = $map[$type];
        }

        if (class_exists($type)) {
            return $type;
        }

        $resolver = $this->resolver();
        $resolved = $resolver($type);
        return $resolved;
    }

    /**
     * Whether a `type` is resolvable. The Generic Factory simply checks if the _FQN_ `type` class exists.
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
        if (isset($map[$type])) {
            $type = $map[$type];
        }

        if (class_exists($type)) {
            return true;
        }

        $resolver = $this->resolver();
        $resolved = $resolver($type);
        if (class_exists($resolved)) {
            return true;
        }

        return false;
    }


    /**
     * Create a class instance with given arguments.
     *
     * How the constructor arguments are passed depends on its type:
     *
     * - if null, no arguments are passed at all.
     * - if it's not an array, it's passed as a single argument.
     * - if it's an associative array, it's passed as a sing argument.
     * - if it's a sequential (numeric keys) array, it's
     *
     * @param string $className The FQN of the class to instanciate.
     * @param mixed  $args      The constructor arguments.
     * @return mixed The created object.
     */
    protected function createClass($className, $args)
    {
        if ($args === null) {
            return new $className();
        }
        if (!is_array($args)) {
            return new $className($args);
        }
        if (count(array_filter(array_keys($args), 'is_string')) > 0) {
            return new $className($args);
        } else {
            /**
             * @todo Use argument unpacking (`return new $className(...$args);`)
             *     when minimum PHP requirement is bumped to 5.6.
             */
            $reflection = new ReflectionClass($className);
            return $reflection->newInstanceArgs($args);
        }
    }

    /**
     * @return callable
     */
    protected function resolver()
    {
        return $this->resolver;
    }

    /**
     * Get the map of all types in `[$type => $class]` format.
     *
     * @return string[]
     */
    protected function map()
    {
        return $this->map;
    }

    /**
     * Add a class name to the available types _map_.
     *
     * @param string $type      The type (class ident).
     * @param string $className The FQN of the class.
     * @throws InvalidArgumentException If the $type parameter is not a striing or the $className class does not exist.
     * @return self
     */
    protected function addClassToMap($type, $className)
    {
        if (!is_string($type)) {
            throw new InvalidArgumentException(
                'Type (class key) must be a string'
            );
        }

        $this->map[$type] = $className;
        return $this;
    }

    /**
     * @param callable $resolver The class resolver instance to use.
     * @return self
     */
    private function setResolver(callable $resolver)
    {
        $this->resolver = $resolver;
        return $this;
    }

    /**
     * Add multiple types, in a an array of `type` => `className`.
     *
     * @param string[] $map The map (key=>classname) to use.
     * @return self
     */
    private function setMap(array $map)
    {
        // Resets (overwrites) map.
        $this->map = [];
        foreach ($map as $type => $className) {
            $this->addClassToMap($type, $className);
        }
        return $this;
    }

    /**
     * Run the callback(s) on the object, if applicable.
     *
     * @param mixed    $obj            The object to pass to callback(s).
     * @param callable $customCallback An optional additional custom callback.
     * @return void
     */
    private function runCallbacks(&$obj, callable $customCallback = null)
    {
        $factoryCallback = $this->callback();
        if (isset($factoryCallback)) {
            $factoryCallback($obj);
        }
        if (isset($customCallback)) {
            $customCallback($obj);
        }
    }
}
