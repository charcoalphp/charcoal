<?php

namespace Charcoal\Property;

use \Exception;
use \InvalidArgumentException;

use \Psr\Cache\CacheItemPoolInterface;

use \Pimple\Container;

use \Charcoal\Factory\FactoryInterface;

use \Charcoal\View\ViewableInterface;

use \Charcoal\Model\ModelInterface;
use \Charcoal\Model\Service\ModelLoader;
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
     * @var CacheItemPoolInterface
     */
    private $cachePool;

    /**
     * @var ModelLoader[]
     */
    static private $modelLoaders = [];

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
     * @param FactoryInterface $factory The factory, to create model objects.
     * @return ObjectProperty Chainable
     */
    private function setModelFactory(FactoryInterface $factory)
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
                sprintf('Model factory not set on object property.')
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

        return $loader;
    }

    /**
     * @param CacheItemPoolInterface $cachePool The PSR-6 Cache.
     * @return void
     */
    private function setCachePool(CacheItemPoolInterface $cachePool)
    {
        $this->cachePool = $cachePool;
    }

    /**
     * @throws Exception If the cache pool was not previously set.
     * @return CacheItemPoolInterface;
     */
    private function cachePool()
    {
        if (!isset($this->cachePool)) {
            throw new Exception(
                'Cache pool was not set.'
            );
        }

        return $this->cachePool;
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
        if ($val === null) {
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
        if ($this->proto === null) {
            $this->proto = $this->modelFactory()->get($this->objType());
        }

        return $this->proto;
    }

    /**
     * @param mixed $val Optional. The value to display.
     * @return string
     */
    public function displayVal($val)
    {
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
            $obj = $this->loadObject($objIdent);

            if ($obj === null) {
                continue;
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
     * Required by `SelectablePropertyInterface`.
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
        $obj = $this->loadObject($choiceIdent);

        if ($obj === null) {
            return null;
        }

        $label  = $this->objPattern($obj);
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
     * Render the choice from the object.
     *
     * @param ModelInterface|ViewableInterface $obj The object or view to render as a label.
     * @return string
     */
    protected function objPattern($obj)
    {
        $pattern = (string)$this->pattern();
        if ($obj instanceof ViewableInterface && $obj->view() !== null) {
            return $obj->renderTemplate($pattern);
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
