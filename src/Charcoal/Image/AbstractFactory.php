<?php
/**
* This file is copied from `charcoal-core` to avoid having it as a dependency, until it's all figured out.
*/

namespace Charcoal\Image;

abstract class AbstractFactory
{
    static protected $instance;

    /**
    * Keeps loaded instances in memory, in `[$type=>$instance]` format.
    * @var array $instances
    */
    protected $instances = [];

    /**
    * Available types, in `[$type=>$classname]` format.
    * @var array $types
    */
    static protected $types = [];

    /**
    * Constructor is protected. Singleton must be created with AbstractFactory::instance()
    */
    protected function __construct()
    {
        // Set as protected. This structure is a singleton.
    }

    /**
    * Singleton instance
    *
    * @return FactoryInterface
    */
    public static function instance()
    {
        if (static::$instance !== null) {
            return static::$instance;
        }
        $class = get_called_class();
        $factory = new $class;
        return $factory;
    }

    /**
    * Create a new instance of a class, by type.
    *
    * @param string $type The type (class ident)
    * @throws \InvalidArgumentException if type is not a string or is not an available type
    * @return mixed The instance / object
    */
    public function create($type)
    {
        if (!is_string($type)) {
            throw new \InvalidArgumentException('Type must be a string');
        }
        if (!$this->is_type_available($type)) {
            throw new \InvalidArgumentException(sprintf('Type "%s" is not a valid type', $type));
        }
        $types = static::types();
        $class = $types[$type];
        $obj = new $class;
        return $obj;
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
        if (!is_string($type)) {
            throw new \InvalidArgumentException('Type must be a string');
        }
        if (isset($this->instances[$type]) && $this->instances[$type] !== null) {
            return $this->instances[$type];
        } else {
            return $this->create($type);
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
        if (!in_array($type, array_keys($types))) {
            return false;
        } else {
            $class = $types[$type];
            if (!class_exists($class)) {
                return false;
            } else {
                return true;
            }
        }
    }

    /**
    * Add a type to the available types
    *
    * @param string $type  The type (class ident)
    * @param string $class The FQN of the class
    * @return boolean Success / Failure
    */
    public static function add_type($type, $class)
    {
        if (!class_exists($class)) {
            return false;
        } else {
            static::$types[$type] = $class;
            return true;
        }
    }

    /**
    * Get the map of all types in `[$type => $class]` format
    *
    * @return array
    */
    public static function types()
    {
        return static::$types;
    }

    /**
    * @param string $ident
    * @return string
    */
    public function ident_to_classname($ident)
    {
        $class = str_replace('/', '\\', $ident);
        
        // Change "foo-bar" to "fooBar"
        $expl = explode('-', $class);
        array_walk(
            $expl,
            function(&$i) {
                $i = ucfirst($i);
            }
        );
        $class = implode('', $expl);

        // Change "/foo/bar" to "\Foo\Bar"
        $expl = explode('\\', $class);
        array_walk(
            $expl,
            function(&$i) {
                $i = ucfirst($i);
            }
        );
        $class = '\\'.trim(implode('\\', $expl), '\\');
        return $class;
    }
}
