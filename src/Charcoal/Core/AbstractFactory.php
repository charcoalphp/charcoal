<?php

namespace Charcoal\Core;

// Dependencies from `PHP`
use \InvalidArgumentException as InvalidArgumentException;

// Local namespace dependencies
use \Charcoal\Core\FactoryInterface as FactoryInterface;

/**
* Full implementation, as Abstract class, of the FactoryInterface
*/
abstract class AbstractFactory implements FactoryInterface
{
    const MODE_CLASS_MAP = 'class_map';
    const MODE_IDENT = 'ident';

    /**
    * Keep latest instance, as singleton copy.
    * @var AbstractFactory $instance
    */
    static protected $instance = [];

    /**
    * @var string $_factory_mode
    */
    protected $_factory_mode;

    /**
    * If a base class is set, then it must be ensured that the
    * @var string $_base_class
    */
    protected $_base_class = '';
    /**
    * @var string $_default_class
    */
    protected $_default_class = '';

    /**
    * Keeps loaded instances in memory, in `[$type => $instance]` format.
    * @var array $_instances
    */
    protected $_instances = [];

    /**
    * Available types, in `[$type => $classname]` format.
    * @var array $_types
    */
    protected $_types = [];

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
        $factory_class = get_called_class();

        if (isset(static::$instance[$factory_class]) && static::$instance[$factory_class] !== null) {
            return static::$instance[$factory_class];
        }

        $factory = new $factory_class;
        static::$instance[$factory_class] = $factory;
        return $factory;
    }

    /**
    * Set the factory's data / properties
    *
    * @param array $data
    * @return
    */
    public function set_data(array $data)
    {
        if (isset($data['factory_mode']) && $data['factory_mode'] !== null) {
            $this->set_factory_mode($data['factory_mode']);
        }
        if (isset($data['base_class']) && $data['base_class'] !== null) {
            $this->set_base_class($data['base_class']);
        }
        if (isset($data['default_class']) && $data['default_class'] !== null) {
            $this->set_default_class($data['default_class']);
        }
        if (isset($data['types']) && $data['types'] !== null) {
            $this->set_types($data['types']);
        }
        return $this;
    }

    /**
    * @param string $mode
    * @throws InvalidArgumentException
    * @return AbstractFactory Chainable
    */
    public function set_factory_mode($mode)
    {
        $valid_modes = [self::MODE_CLASS_MAP, self::MODE_IDENT];
        if (!in_array($mode, $valid_modes)) {
            throw new InvalidArgumentException('Not a valid factory mode');
        }
        $this->_factory_mode = $mode;
        return $this;
    }

    /**
    * There are 2 different factory modes:
    * - class_map: Use the $type array for class map matching the ident
    * - ident: Try to deduce the class name from the ident itself
    *
    * @return string
    */
    public function factory_mode()
    {
        if (!$this->_factory_mode) {
            $this->_factory_mode = self::MODE_CLASS_MAP;
        }
        return $this->_factory_mode;
    }

    /**
    * If a base class is set, then it must be ensured that the created objects
    * are `instanceof` this base class.
    * @param string $classname
    * @throws InvalidArgumentException
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
                sprintf('Can not set "%s" as base class: Invalid class name', $classname)
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
    * @param string $type The type (class ident)
    * @throws Exception
    * @throws InvalidArgumentException if type is not a string or is not an available type
    * @return mixed The instance / object
    */
    public function create($type)
    {
        if (!is_string($type)) {
            throw new InvalidArgumentException('Type must be a string.');
        }
        if (!$this->is_type_available($type)) {
            $default_class = $this->default_class();
            if ($default_class !== '') {
                return new $default_class;
            } else {
                throw new InvalidArgumentException(sprintf('Type "%s" is not a valid type.', $type));
            }
        }
        $classname = $this->type_to_classname($type);
        $obj = new $classname;

        // Ensure base class is respected, if set.
        $base_class = $this->base_class();
        if ($base_class !== '' && !($obj instanceof $base_class)) {
            throw new Exception(sprintf('Object is not a valid %s class', $base_class));
        }

        $this->_instances[$type] = $obj;
        return $obj;
    }

    /**
    * Get (load or create) an instance of a class, by type.
    *
    * Unlike create (which always call a `new` instance), this function first tries to load / reuse
    * an already created object of this type, from memory.
    *
    * @param string $type The type (class ident)
    * @throws InvalidArgumentException if type is not a string
    * @return mixed The instance / object
    */
    public function get($type)
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
    public function type_to_classname($type)
    {
        $classname = '';
        $mode = $this->factory_mode();
        switch ($mode) {
            case self::MODE_IDENT:
                $classname = $this->ident_to_classname($type);
                break;

            case self::MODE_CLASS_MAP:
                $types = $this->types();
                if (!isset($types[$type])) {
                    throw new InvalidArgumentException('Invalid type');
                }
                $classname = $types[$type];
                break;
        }

        return $classname;
    }

    /**
    * Returns wether a type is available
    *
    * @param string $type The type to check
    * @return boolean True if the type is available, false if not
    */
    public function is_type_available($type)
    {
        $is_available = false;
        $mode = $this->factory_mode();
        switch ($mode) {
            case self::MODE_IDENT:
                $class_name = $this->ident_to_classname($type);
                $is_available = class_exists($class_name);
                break;

            case self::MODE_CLASS_MAP:
                $types = $this->types();
                if (!in_array($type, array_keys($types))) {
                    $is_available = false;
                } else {
                    $class_name = $types[$type];
                    $is_available = class_exists($class_name);
                }
                break;
        }

        return $is_available;
    }

    /**
    * Add multiple types, in a an array of `type` => `classname`.
    *
    * @param array $types
    * @return AbstractFactory Chainable
    */
    public function set_types(array $types)
    {
        $this->_types = [];
        foreach ($types as $type => $classname) {
            $this->add_type($type, $classname);
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
    public function add_type($type, $classname)
    {
        if (!class_exists($classname)) {
            throw new InvalidArgumentException(
                sprintf('Class "%s" is not a valid class name.', $classname)
            );
        }

        $this->_types[$type] = $classname;
        return $this;
    }

    /**
    * Get the map of all types in `[$type => $class]` format
    *
    * @return array
    */
    public function types()
    {
        return $this->_types;
    }

    /**
    * @param string $ident
    * @return string
    */
    public function ident_to_classname($ident)
    {
        // Change "foo-bar" to "fooBar"
        $expl = explode('-', $ident);
        array_walk(
            $expl,
            function(&$i) {
                $i = ucfirst($i);
            }
        );
        $ident = implode('', $expl);

        // Change "/foo/bar" to "\Foo\Bar"
        $class = str_replace('/', '\\', $ident);
        $expl  = explode('\\', $class);
        array_walk(
            $expl,
            function(&$i) {
                $i = ucfirst($i);
            }
        );

        $class = '\\'.trim(implode('\\', $expl), '\\');
        $class = $this->factory_class($class);
        return $class;
    }

    /**
    * @param string
    * @return string
    */
    public function factory_class($ident)
    {
        return $ident;
    }
}
