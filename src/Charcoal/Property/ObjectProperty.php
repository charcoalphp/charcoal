<?php

namespace Charcoal\Property;

use \RuntimeException;
use \InvalidArgumentException;

// From PSR-6
use \Psr\Cache\CacheItemPoolInterface;

// From Pimple
use \Pimple\Container;

// From 'charcoal-core'
use \Charcoal\Loader\CollectionLoader;
use \Charcoal\Model\ModelInterface;
use \Charcoal\Model\Service\ModelLoader;
use \Charcoal\Source\StorableInterface;

// From 'charcoal-factory'
use \Charcoal\Factory\FactoryInterface;

// From 'charcoal-view'
use \Charcoal\View\ViewInterface;
use \Charcoal\View\ViewableInterface;

// From 'charcoal-property'
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
     * Store the PSR-6 caching service.
     *
     * @var CacheItemPoolInterface
     */
    private $cachePool;

    /**
     * Store all model loaders.
     *
     * @var ModelLoader[]
     */
    private static $modelLoaders = [];

    /**
     * Store the factory instance for the current class.
     *
     * @var FactoryInterface
     */
    private $modelFactory;

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
        $this->setCachePool($container['cache']);
    }

    /**
     * @return string
     */
    public function type()
    {
        return 'object';
    }

    /**
     * Set an object model factory.
     *
     * @param FactoryInterface $factory The model factory, to create objects.
     * @return self
     */
    protected function setModelFactory(FactoryInterface $factory)
    {
        $this->modelFactory = $factory;

        return $this;
    }

    /**
     * Retrieve the object model factory.
     *
     * @throws RuntimeException If the model factory was not previously set.
     * @return FactoryInterface
     */
    public function modelFactory()
    {
        if (!isset($this->modelFactory)) {
            throw new RuntimeException(
                sprintf('Model Factory is not defined for "%s"', get_class($this))
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
    private function setCollectionLoader(CollectionLoader $loader)
    {
        $this->collectionLoader = $loader;

        return $this;
    }

    /**
     * Retrieve the model collection loader.
     *
     * @throws RuntimeException If the collection loader was not previously set.
     * @return CollectionLoader
     */
    protected function collectionLoader()
    {
        if (!isset($this->collectionLoader)) {
            throw new RuntimeException(
                sprintf('Collection Loader is not defined for "%s"', get_class($this))
            );
        }

        $proto  = $this->proto();
        $loader = $this->collectionLoader;
        $loader->setModel($proto);

        return $loader;
    }

    /**
     * Set the cache service.
     *
     * @param  CacheItemPoolInterface $cache A PSR-6 compliant cache pool instance.
     * @return MetadataLoader Chainable
     */
    private function setCachePool(CacheItemPoolInterface $cache)
    {
        $this->cachePool = $cache;

        return $this;
    }

    /**
     * Retrieve the cache service.
     *
     * @throws RuntimeException If the cache service was not previously set.
     * @return CacheItemPoolInterface
     */
    private function cachePool()
    {
        if (!isset($this->cachePool)) {
            throw new RuntimeException(
                sprintf('Cache Pool is not defined for "%s"', get_class($this))
            );
        }

        return $this->cachePool;
    }

    /**
     * Set the object type to build the choices from.
     *
     * @param  string $objType The object type.
     * @throws InvalidArgumentException If the object type is not a string.
     * @return ObjectPropertyChainable
     */
    public function setObjType($objType)
    {
        if (!is_string($objType)) {
            throw new InvalidArgumentException(
                'Property object type ("obj_type") must be a string.'
            );
        }

        $this->objType = $objType;

        return $this;
    }

    /**
     * Retrieve the object type to build the choices from.
     *
     * @throws RuntimeException If the object type was not previously set.
     * @return string
     */
    public function objType()
    {
        if ($this->objType === null) {
            throw new RuntimeException(sprintf(
                'Missing object type ("obj_type"). Invalid property "%s".',
                $this->ident()
            ));
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
                'The render pattern must be a string.'
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
            $key   = $proto->p($proto->key());

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
        $key   = $proto->p($proto->key());

        return $key->sqlPdoType();
    }

    /**
     * Always return IDs.
     *
     * @param mixed $val Value to be parsed.
     * @return mixed
     */
    public function parseOne($val)
    {
        if ($val instanceof StorableInterface) {
            return $val->id();
        } else {
            return $val;
        }
    }

    /**
     * Get the property's value in a format suitable for storage.
     *
     * @param mixed $val Optional. The value to convert to storage value.
     * @return mixed
     */
    public function storageVal($val)
    {
        if ($val === null || $val === '') {
            // Do not json_encode NULL values
            return null;
        }

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
     * Retrieve a singleton of the {self::$objType} for prototyping.
     *
     * @return ModelInterface
     */
    public function proto()
    {
        return $this->modelFactory()->get($this->objType());
    }

    /**
     * @param  mixed $val     The value to to convert for display.
     * @param  array $options Optional display options.
     * @return string
     */
    public function displayVal($val, array $options = [])
    {
        if ($val === null) {
            return '';
        }

        if ($val instanceof ModelInterface) {
            $propertyVal = $this->renderObjPattern($val);

            if (empty($propertyVal) && !is_numeric($propertyVal)) {
                $propertyVal = $val->id();
            }

            return $propertyVal;
        }

        if ($this->l10n()) {
            $propertyValue = $this->l10nVal($val, $options);
            if ($propertyValue === null) {
                return '';
            }
        } else {
            $propertyValue = $val;
        }

        $separator = $this->multipleSeparator();

        if ($this->multiple()) {
            if (!is_array($propertyValue)) {
                $propertyValue = explode($separator, $propertyValue);
            }
        } else {
            $propertyValue = (array)$propertyValue;
        }

        $values = [];
        foreach ($propertyValue as $val) {
            $label = null;
            if ($val instanceof ModelInterface) {
                $label = $this->renderObjPattern($val);
            } else {
                $obj = $this->loadObject($val);
                if (is_object($obj)) {
                    $label = $this->renderObjPattern($obj);
                }
            }

            if (empty($label) && !is_numeric($label)) {
                $label = $val;
            }

            $values[] = $label;
        }

        if ($separator === ',') {
            $separator = ', ';
        }

        return implode($separator, $values);
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
     * Required by `SelectablePropertyInterface`.
     *
     * @return array
     */
    public function choices()
    {
        $choices = [];

        $proto = $this->proto();
        if (!$proto->source()->tableExists()) {
            return $choices;
        }

        $loader = $this->collectionLoader();

        $orders = $this->orders();
        if ($orders) {
            $loader->setOrders($orders);
        }

        $filters = $this->filters();
        if ($filters) {
            $loader->setFilters($filters);
        }

        $objects = $loader->load();
        foreach ($objects as $obj) {
            $choice = $this->choice($obj);

            if ($choice !== null) {
                $choices[$choice['value']] = $choice;
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
        $obj = $this->loadObject($choiceIdent);

        if ($obj === null) {
            return null;
        }

        $label  = $this->renderObjPattern($obj);
        $choice = [
            'value' => $obj->id(),
            'label' => $label,
            'title' => $label
        ];

        if (is_callable([$obj, 'icon'])) {
            $choice['icon'] = $obj->icon();
        }

        return $choice;
    }

    /**
     * Render the given object.
     *
     * @param  ModelInterface|ViewableInterface $obj     The object or view to render as a label.
     * @param  string|null                      $pattern Optional. The render pattern to render.
     * @throws InvalidArgumentException If the pattern is not a string.
     * @return string
     */
    protected function renderObjPattern($obj, $pattern = null)
    {
        if ($pattern === null) {
            $pattern = $this->pattern();
        }

        if (!is_string($pattern)) {
            throw new InvalidArgumentException(
                'The render pattern must be a string.'
            );
        }

        if ($pattern === '') {
            return '';
        }

        if (strpos($pattern, '{{') === false) {
            return (string)$obj[$pattern];
        }

        if (($obj instanceof ViewableInterface) && ($obj->view() instanceof ViewInterface)) {
            return $obj->renderTemplate($pattern);
        } else {
            $callback = function ($matches) use ($obj) {
                $prop = trim($matches[1]);
                return (string)$obj[$prop];
            };

            return preg_replace_callback('~\{\{\s*(.*?)\s*\}\}~i', $callback, $pattern);
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
    protected function loadObject($id)
    {
        if ($id instanceof ModelInterface) {
            return $id;
        }

        $obj = $this->modelLoader()->load($id);
        if (!$obj->id()) {
            return null;
        } else {
            return $obj;
        }
    }

    /**
     * @return ModelLoader
     */
    private function modelLoader()
    {
        $objType = $this->objType();
        if (isset(self::$modelLoaders[$objType])) {
            return self::$modelLoaders[$objType];
        }

        self::$modelLoaders[$objType] = new ModelLoader([
            'logger'    => $this->logger,
            'obj_type'  => $objType,
            'factory'   => $this->modelFactory(),
            'cache'     => $this->cachePool()
        ]);

        return self::$modelLoaders[$objType];
    }
}
