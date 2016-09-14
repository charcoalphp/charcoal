<?php

namespace Charcoal\Property;

use \Exception;
use \InvalidArgumentException;

use \Pimple\Container;

use \Charcoal\Factory\FactoryInterface;

use \Charcoal\View\ViewableInterface;

use \Charcoal\Model\ModelInterface;
use \Charcoal\Loader\CollectionLoader;
use \Charcoal\Source\StorableInterface;
use \Charcoal\Translation\TranslationConfig;

use \Charcoal\Property\AbstractProperty;
use \Charcoal\Property\SelectablePropertyInterface;

/**
 * Object Property holds a reference to an external object.
 *
 * The object property implements the full `SelectablePropertyInterface` without using
 * its accompanying trait. (`set_choices`, `add_choice`, `choices`, `has_choice`, `choice`).
 */
class ObjectProperty extends AbstractProperty implements SelectablePropertyInterface
{
    /**
     * A store of cached objects.
     *
     * @var ModelInterface[] $objectCache
     */
    public static $objectCache = [];

    /**
     * The object type to build the choices from.
     *
     * @var string
     */
    private $objType;

    /**
     * The pattern for rendering the choice as a label.
     *
     * @var string
     */
    private $pattern = '{{name}}';

    /**
     * The available selectable choices.
     *
     * This collection is built from selected {@see self::$objType}.
     *
     * @var array
     */
    protected $choices = [];

    /**
     * Store the factory instance for the current class.
     *
     * @var FactoryInterface
     */
    private $modelFactory;

    /**
     * Store a reference to the {@see self::$objType} model.
     *
     * @var ModelInterface
     */
    private $proto;

    /**
     * Store the collection loader for the current class.
     *
     * @var CollectionLoader
     */
    private $collectionLoader;

    /**
     * The rules for sorting the collection of objects.
     *
     * @var array
     */
    protected $orders;

    /**
     * The rules for filtering the collection of objects.
     *
     * @var array
     */
    protected $filters;

    /**
     * Inject dependencies from a DI Container.
     *
     * @param  Container $container A dependencies container instance.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->setModelFactory($container['model/factory']);
        $this->setCollectionLoader($container['model/collection/loader']);
    }

    /**
     * @return string
     */
    public function type()
    {
        return 'object';
    }

    /**
     * @param FactoryInterface $factory The factory, to create model objects.
     * @return ObjectProperty Chainable
     */
    public function setModelFactory(FactoryInterface $factory)
    {
        $this->modelFactory = $factory;
        return $this;
    }

    /**
     * @throws Exception If the model factory is not set.
     * @return FactoryInterface
     */
    private function modelFactory()
    {
        if ($this->modelFactory === null) {
            throw new Exception(
                sprintf('Model factory not set on object property "%s".')
            );
        }
        return $this->modelFactory;
    }

    /**
     * Set a model collection loader.
     *
     * @param CollectionLoader $loader The collection loader.
     * @return self
     */
    protected function setCollectionLoader(CollectionLoader $loader)
    {
        $this->collectionLoader = $loader;

        return $this;
    }

    /**
     * Retrieve the model collection loader.
     *
     * @throws Exception If the collection loader was not previously set.
     * @return CollectionLoader
     */
    protected function collectionLoader()
    {
        if (!isset($this->collectionLoader)) {
            throw new Exception(
                sprintf('Collection Loader is not defined for "%s"', get_class($this))
            );
        }

        $proto  = $this->proto();
        $loader = $this->collectionLoader;
        $loader->setModel($proto);

        /** @todo Remove this condition in favor of end-developer defining this condition in property definition. */
        if ($proto->hasProperty('active')) {
            $loader->addFilter('active', true);
        }

        return $loader;
    }

    /**
     * @param string $objType The object type.
     * @throws InvalidArgumentException If the object type is not a string.
     * @return ObjectPropertyChainable
     */
    public function setObjType($objType)
    {
        if (!is_string($objType)) {
            throw new InvalidArgumentException(
                'Can not set property\'s object type: "obj_type" needs to be a string'
            );
        }
        $this->objType = $objType;
        return $this;
    }

    /**
     * @throws Exception If the object type was not previously set.
     * @return string
     */
    public function objType()
    {
        if ($this->objType === null) {
            throw new Exception(
                sprintf('No object type ("obj_type") defined. Invalid property "%s"', $this->ident())
            );
        }
        return $this->objType;
    }

    /**
     * @param string $pattern The render pattern.
     * @throws InvalidArgumentException If the pattern is not a string.
     * @return ObjectProperty Chainable
     */
    public function setPattern($pattern)
    {
        if (!is_string($pattern)) {
            throw new InvalidArgumentException(
                'Can not set property object pattern, needs to be a string.'
            );
        }
        $this->pattern = $pattern;
        return $this;
    }

    /**
     * @return string
     */
    public function pattern()
    {
        return $this->pattern;
    }

    /**
     * @return string
     */
    public function sqlExtra()
    {
        return '';
    }

    /**
     * @return string
     */
    public function sqlType()
    {
        if ($this->multiple() === true) {
            return 'TEXT';
        } else {
            // Read from proto's key
            $proto = $this->proto();
            $key = $proto->p($proto->key());
            return $key->sqlType();
        }
    }

    /**
     * @return integer
     */
    public function sqlPdoType()
    {
        // Read from proto's key
        $proto = $this->proto();
        $key = $proto->p($proto->key());
        return $key->sqlPdoType();
    }


    /**
     * At this point, does nothing but return
     * the actual value. Other properties could
     * parse values such as ObjectProperty who
     * could parse objects into object IDs.
     *
     * @param mixed $val Value to be parsed.
     * @return mixed
     */
    public function parseVal($val = null)
    {
        if ($val instanceof StorableInterface) {
            return $val->id();
        }

        if ($this->multiple()) {
            $out = [];
            foreach ($val as $i => $v) {
                if ($v instanceof StorableInterface) {
                    $out[] = $v->id();
                } elseif (strlen($v)) {
                    $out[] = $v;
                }
            }
            $val = $out;
        }

        return $val;
    }

    /**
     * Get the property's value in a format suitable for storage.
     *
     * @param mixed $val Optional. The value to convert to storage value.
     * @return mixed
     */
    public function storageVal($val = null)
    {
        if ($val === null) {
            $val = $this->val();
        }
        if ($val === null) {
            // Do not json_encode NULL values
            return null;
        }

        // Get parsedVal
        $val = $this->parseVal($val);

        if ($this->multiple()) {
            if (is_array($val)) {
                $val = implode($this->multipleSeparator(), $val);
            }
        }

        if (!is_scalar($val)) {
            return json_encode($val);
        }
        return $val;
    }

    /**
     * @return mixed
     */
    public function save()
    {
        return $this->val();
    }

    /**
     * Retrieve a singleton of the {self::$objType} for prototyping.
     *
     * @return ModelInterface
     */
    public function proto()
    {
        if ($this->proto === null) {
            $this->proto = $this->modelFactory()->get($this->objType());
        }
        return $this->proto;
    }

    /**
     * @param mixed $val Optional. The value to display.
     * @return string
     */
    public function displayVal($val = null)
    {
        if ($val === null) {
            $val = $this->val();
        }

        if ($val === null) {
            return '';
        }

        $propertyValue = $val;

        if ($this->l10n() === true) {
            $translator = TranslationConfig::instance();

            $propertyValue = $propertyValue[$translator->currentLanguage()];
        }

        if ($this->multiple() === true) {
            if (!is_array($propertyValue)) {
                $propertyValue = explode($this->multipleSeparator(), $propertyValue);
            }
        } else {
            $propertyValue = [$propertyValue];
        }

        $names = [];
        foreach ($propertyValue as $objIdent) {
            if ($objIdent instanceof ModelInterface) {
                $obj = $objIdent;
            } else {
                $obj = $this->loadObject($objIdent);

                if ($obj === null) {
                    continue;
                }
            }

            $names[] = $this->objPattern($obj);
        }
        return implode(', ', $names);
    }

    /**
     * Fulfills the SelectableProperty interface, but does nothing.
     *
     * @param array $choices The array of choice structures.
     * @return SelectablePropertyInterface Chainable.
     */
    public function setChoices(array $choices)
    {
        unset($choices);
        $this->logger->debug('Choices can not be set for object properties. They are auto-generated from objects.');
        return $this;
    }

    /**
     * Add a choice to the available choices map.
     *
     * @param string       $choiceIdent The choice identifier (will be key / default ident).
     * @param string|array $choice      A string representing the choice label or a structure.
     * @return SelectablePropertyInterface Chainable.
     */
    public function addChoice($choiceIdent, $choice)
    {
        unset($choiceIdent, $choice);
        $this->logger->debug('Choices can not be added for object properties. They are auto-generated from objects.');
        return $this;
    }

    /**
     * Set the rules for sorting the collection of objects.
     *
     * @param  array $orders An array of orders.
     * @return ObjectProperty Chainable
     */
    public function setOrders(array $orders)
    {
        $this->orders = $orders;

        return $this;
    }

    /**
     * Retrieve the rules for sorting the collection of objects.
     *
     * @return array
     */
    public function orders()
    {
        return $this->orders;
    }

    /**
     * Set the rules for filtering the collection of objects.
     *
     * @param  array $filters An array of filters.
     * @return ObjectProperty Chainable
     */
    public function setFilters(array $filters)
    {
        $this->filters = $filters;

        return $this;
    }

    /**
     * Retrieve the rules for filtering the collection of objects.
     *
     * @return array
     */
    public function filters()
    {
        return $this->filters;
    }

    /**
     * Get the choices array map.
     *
     * @return array
     */
    public function choices()
    {
        $loader = $this->collectionLoader();
        $orders = $this->orders();
        if ($orders) {
            $loader->setOrders($orders);
        }

        $filters = $this->filters();
        if ($filters) {
            $loader->setFilters($filters);
        }

        $choices = [];
        $objects = $loader->load();
        foreach ($objects as $obj) {
            $choice = $this->choice($obj);

            if ($choice !== null) {
                $choices[$obj->id()] = $choice;
            }
        }

        return $choices;
    }

    /**
     * Returns wether a given choiceIdent exists or not.
     *
     * @param string $choiceIdent The choice identifier.
     * @return boolean True / false wether the choice exists or not.
     */
    public function hasChoice($choiceIdent)
    {
        $obj = $this->loadObject($choiceIdent);

        return ($obj instanceof ModelInterface && $obj->id() == $choiceIdent);
    }

    /**
     * Returns a choice structure for a given ident.
     *
     * @param string|ModelInterface $choiceIdent The choice ident or object to format.
     * @return mixed The matching choice.
     */
    public function choice($choiceIdent)
    {
        if ($choiceIdent instanceof ModelInterface) {
            $obj = $choiceIdent;
        } else {
            $obj = $this->loadObject($choiceIdent);

            if ($obj === null) {
                return null;
            }
        }

        $label  = $this->objPattern($obj);
        $choice = [
            'value' => $obj->id(),
            'label' => $label,
            'title' => $label
        ];

        if (is_callable([ $obj, 'icon' ])) {
            $choice['icon'] = $obj->icon();
        }

        return $choice;
    }

    /**
     * Render the choice from the object.
     *
     * @param ModelInterface|ViewableInterface $obj The object or view to render as a label.
     * @return string
     */
    protected function objPattern($obj)
    {
        $pattern = (string)$this->pattern();
        if ($obj instanceof ViewableInterface && $obj->view() !== null) {
            return $obj->render($pattern);
        } else {
            $cb = function ($matches) use ($obj) {
                $method = trim($matches[1]);
                if (method_exists($obj, $method)) {
                    return call_user_func([$obj, $method]);
                } elseif (isset($obj[$method])) {
                    return $obj[$method];
                } else {
                    return '';
                }
            };
            return preg_replace_callback('~\{\{\s*(.*?)\s*\}\}~i', $cb, $pattern);
        }
    }

    /**
     * Retrieve an object by its ID.
     *
     * Loads the object from the cache store or from the storage source.
     *
     * @param mixed $id Object id.
     * @return ModelInterface
     */
    private function loadObject($id)
    {
        $cached = $this->loadObjectFromCache($id);
        if ($cached !== null) {
            return $cached;
        }

        $obj = $this->loadObjectFromSource($id);

        if ($obj !== null) {
            $this->addObjectToCache($obj);
        }

        return $obj;
    }

    /**
     * Retrieve an object from the storage source by its ID.
     *
     * @param mixed $id The object id.
     * @return null|ModelInterface
     */
    private function loadObjectFromSource($id)
    {
        $obj = $this->modelFactory()->create($this->objType());
        $obj->load($id);

        if ($obj->id()) {
            return $obj;
        } else {
            return null;
        }
    }

    /**
     * Retrieve an object from the cache store by its ID.
     *
     * @param mixed $id The object id.
     * @return null|ModelInterface
     */
    private function loadObjectFromCache($id)
    {
        $objType = $this->objType();
        if (isset(static::$objectCache[$objType][$id])) {
            return static::$objectCache[$objType][$id];
        } else {
            return null;
        }
    }

    /**
     * Add an object to the cache store.
     *
     * @param ModelInterface $obj The object to store.
     * @return ObjectProperty Chainable
     */
    private function addObjectToCache(ModelInterface $obj)
    {
        static::$objectCache[$this->objType()][$obj->id()] = $obj;

        return $this;
    }
}
