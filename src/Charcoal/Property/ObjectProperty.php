<?php

namespace Charcoal\Property;

use Traversable;
use RuntimeException;
use InvalidArgumentException;

// From PSR-6
use Psr\Cache\CacheItemPoolInterface;

// From Pimple
use Pimple\Container;

// From 'charcoal-core'
use Charcoal\Loader\CollectionLoader;
use Charcoal\Model\ModelInterface;
use Charcoal\Model\Service\ModelLoader;
use Charcoal\Source\StorableInterface;

// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;

// From 'charcoal-view'
use Charcoal\View\ViewableInterface;

// From 'charcoal-translator'
use Charcoal\Translator\Translation;

// From 'charcoal-property'
use Charcoal\Property\AbstractProperty;
use Charcoal\Property\SelectablePropertyInterface;

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
     * The rules for pagination the collection of objects.
     *
     * @var array|null
     */
    protected $pagination;

    /**
     * The rules for sorting the collection of objects.
     *
     * @var array|null
     */
    protected $orders;

    /**
     * The rules for filtering the collection of objects.
     *
     * @var array|null
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
    protected static $modelLoaders = [];

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
     * @param  FactoryInterface $factory The model factory, to create objects.
     * @return ObjectProperty Chainable
     */
    private function setModelFactory(FactoryInterface $factory)
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
    protected function modelFactory()
    {
        if (!isset($this->modelFactory)) {
            throw new RuntimeException(sprintf(
                'Model Factory is not defined for "%s"',
                get_class($this)
            ));
        }

        return $this->modelFactory;
    }

    /**
     * Set a model collection loader.
     *
     * @param  CollectionLoader $loader The collection loader.
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
        if ($this->collectionLoader === null) {
            throw new RuntimeException(sprintf(
                'Collection Loader is not defined for "%s"',
                get_class($this)
            ));
        }

        return $this->collectionLoader;
    }

    /**
     * Retrieve the prepared model collection loader.
     *
     * @return CollectionLoader
     */
    protected function collectionModelLoader()
    {
        $loader = $this->collectionLoader();

        if (!$loader->hasModel()) {
            $loader->setModel($this->proto());

            $pagination = $this->pagination();
            if (!empty($pagination)) {
                $loader->setPagination($pagination);
            }

            $orders = $this->orders();
            if (!empty($orders)) {
                $loader->setOrders($orders);
            }

            $filters = $this->filters();
            if (!empty($filters)) {
                $loader->setFilters($filters);
            }
        }

        return $loader;
    }

    /**
     * Set the cache service.
     *
     * @param  CacheItemPoolInterface $cache A PSR-6 compliant cache pool instance.
     * @return ObjectProperty Chainable
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
    protected function cachePool()
    {
        if (!isset($this->cachePool)) {
            throw new RuntimeException(sprintf(
                'Cache Pool is not defined for "%s"',
                get_class($this)
            ));
        }

        return $this->cachePool;
    }

    /**
     * Set the object type to build the choices from.
     *
     * @param  string $objType The object type.
     * @throws InvalidArgumentException If the object type is not a string.
     * @return ObjectProperty Chainable
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
     * @param  string $pattern The render pattern.
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
     * @param  mixed $val Value to be parsed.
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
     * @param  mixed $val Optional. The value to convert to storage value.
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
     * @param  mixed $val     Optional. The value to to convert for input.
     * @param  array $options Optional input options.
     * @return string
     */
    public function inputVal($val, array $options = [])
    {
        unset($options);

        if ($val === null) {
            return '';
        }

        if (is_string($val)) {
            return $val;
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
     * @param  mixed $val     The value to to convert for display.
     * @param  array $options Optional display options.
     * @return string
     */
    public function displayVal($val, array $options = [])
    {
        if ($val === null) {
            return '';
        }

        if (isset($options['pattern'])) {
            $pattern = $options['pattern'];
        } else {
            $pattern = null;
        }

        if (isset($options['lang'])) {
            $lang = $options['lang'];
        } else {
            $lang = null;
        }

        if ($val instanceof ModelInterface) {
            $propertyVal = $this->renderObjPattern($val, $pattern, $lang);

            if (empty($propertyVal) && !is_numeric($propertyVal)) {
                $propertyVal = $val->id();
            }

            return $propertyVal;
        }

        /** Parse multilingual values */
        if ($this->l10n()) {
            $propertyValue = $this->l10nVal($val, $options);
            if ($propertyValue === null) {
                return '';
            }
        } elseif ($val instanceof Translation) {
            $propertyValue = (string)$val;
        } else {
            $propertyValue = $val;
        }

        $separator = $this->multipleSeparator();

        /** Parse multiple values / ensure they are of array type. */
        if ($this->multiple()) {
            if (!is_array($propertyValue)) {
                $propertyValue = explode($separator, $propertyValue);
            }
        } else {
            $propertyValue = (array)$propertyValue;
        }

        if ($separator === ',') {
            $separator = ', ';
        }

        $values = [];
        foreach ($propertyValue as $val) {
            $label = null;
            if ($val instanceof ModelInterface) {
                $label = $this->renderObjPattern($val, $pattern, $lang);
            } else {
                $obj = $this->loadObject($val);
                if (is_object($obj)) {
                    $label = $this->renderObjPattern($obj, $pattern, $lang);
                }
            }

            if (empty($label) && !is_numeric($label)) {
                $label = $val;
            }

            $values[] = $label;
        }

        return implode($separator, $values);
    }

    /**
     * Set the available choices.
     *
     * @param  array $choices One or more choice structures.
     * @return ObjectProperty Chainable
     */
    public function setChoices(array $choices)
    {
        unset($choices);

        $this->logger->debug(
            'Choices can not be set for object properties. They are auto-generated from objects.'
        );

        return $this;
    }

    /**
     * Merge the available choices.
     *
     * @param  array $choices One or more choice structures.
     * @return ObjectProperty Chainable
     */
    public function addChoices(array $choices)
    {
        unset($choices);

        $this->logger->debug(
            'Choices can not be added for object properties. They are auto-generated from objects.'
        );

        return $this;
    }

    /**
     * Add a choice to the available choices.
     *
     * @param  string       $choiceIdent The choice identifier (will be key / default ident).
     * @param  string|array $choice      A string representing the choice label or a structure.
     * @return ObjectProperty Chainable
     */
    public function addChoice($choiceIdent, $choice)
    {
        unset($choiceIdent, $choice);

        $this->logger->debug(
            'Choices can not be added for object properties. They are auto-generated from objects.'
        );

        return $this;
    }

    /**
     * Set the rules for pagination the collection of objects.
     *
     * @param  array $pagination Pagination settings.
     * @return ObjectProperty Chainable
     */
    public function setPagination(array $pagination)
    {
        $this->pagination = $pagination;

        return $this;
    }

    /**
     * Retrieve the rules for pagination the collection of objects.
     *
     * @return array|null
     */
    public function pagination()
    {
        return $this->pagination;
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
     * @return array|null
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
     * @return array|null
     */
    public function filters()
    {
        return $this->filters;
    }

    /**
     * Determine if choices are available.
     *
     * @return boolean
     */
    public function hasChoices()
    {
        if (!$this->proto()->source()->tableExists()) {
            return false;
        }

        return ($this->collectionModelLoader()->loadCount() > 0);
    }

    /**
     * Retrieve the available choice structures.
     *
     * @see    SelectablePropertyInterface::choices()
     * @return array
     */
    public function choices()
    {
        $proto = $this->proto();
        if (!$proto->source()->tableExists()) {
            return [];
        }

        $objects = $this->collectionModelLoader()->load();
        $choices = $this->parseChoices($objects);

        return $choices;
    }

    /**
     * Determine if the given choice is available.
     *
     * @see    SelectablePropertyInterface::hasChoice()
     * @param  string $choiceIdent The choice identifier to lookup.
     * @return boolean
     */
    public function hasChoice($choiceIdent)
    {
        $obj = $this->loadObject($choiceIdent);

        return ($obj instanceof ModelInterface && $obj->id() == $choiceIdent);
    }

    /**
     * Retrieve the structure for a given choice.
     *
     * The method can be used to format an object into a choice structure.
     *
     * @see    SelectablePropertyInterface::choice()
     * @param  string $choiceIdent The choice identifier to lookup or object to format.
     * @return mixed The matching choice.
     */
    public function choice($choiceIdent)
    {
        $obj = $this->loadObject($choiceIdent);

        if ($obj === null) {
            return null;
        }

        $choice = $this->parseChoice($obj);

        return $choice;
    }

    /**
     * Parse the given objects into choice structures.
     *
     * @param  ModelInterface[]|Traversable $objs One or more objects to format.
     * @throws InvalidArgumentException If the collection of objects is not iterable.
     * @return array Returns a collection of choice structures.
     */
    protected function parseChoices($objs)
    {
        if (!is_array($objs) && !$objs instanceof Traversable) {
            throw new InvalidArgumentException('Must be iterable');
        }

        $parsed = [];
        foreach ($objs as $choice) {
            $choice = $this->parseChoice($choice);
            if ($choice !== null) {
                $choiceIdent = $choice['value'];
                $parsed[$choiceIdent] = $choice;
            }
        }

        return $parsed;
    }

    /**
     * Parse the given value into a choice structure.
     *
     * @param  ModelInterface $obj An object to format.
     * @return array Returns a choice structure.
     */
    protected function parseChoice(ModelInterface $obj)
    {
        $label  = $this->renderObjPattern($obj);
        $choice = [
            'value' => $obj->id(),
            'label' => $label,
            'title' => $label
        ];

        /** @todo Move to {@see \Charcoal\Admin\Property\AbstractSelectableInput::choiceObjMap()} */
        if (is_callable([$obj, 'icon'])) {
            $choice['icon'] = $obj->icon();
        }

        return $choice;
    }

    /**
     * Retrieve the label for a given choice.
     *
     * @see    SelectablePropertyInterface::choiceLabel()
     * @param  string|array|ModelInterface $choice The choice identifier to lookup.
     * @throws InvalidArgumentException If the choice is invalid.
     * @return string|null Returns the label. Otherwise, returns the raw value.
     */
    public function choiceLabel($choice)
    {
        if ($choice === null) {
            return null;
        }

        if (is_array($choice)) {
            if (isset($choice['label'])) {
                return $choice['label'];
            } elseif (isset($choice['value'])) {
                return $choice['value'];
            } else {
                throw new InvalidArgumentException(
                    'Choice structure must contain a "label" or "value".'
                );
            }
        }

        $obj = $this->loadObject($choice);

        if ($obj === null) {
            return $choice;
        }

        return $this->renderObjPattern($obj);
    }

    /**
     * Render the given object.
     *
     * @see    Move to \Charcoal\Admin\Property\AbstractSelectableInput::choiceObjMap()
     * @param  ModelInterface $obj     The object or view to render as a label.
     * @param  string|null    $pattern Optional. The render pattern to render.
     * @param  string|null    $lang    The language to return the value in.
     * @throws InvalidArgumentException If the pattern is not a string.
     * @return string
     */
    protected function renderObjPattern(ModelInterface $obj, $pattern = null, $lang = null)
    {
        if ($pattern === null) {
            $pattern = $this->pattern();
        } elseif (!is_string($pattern)) {
            throw new InvalidArgumentException(
                'The render pattern must be a string.'
            );
        }

        if ($pattern === '') {
            return '';
        }

        if ($lang === null) {
            $lang = $this->translator()->getLocale();
        } elseif (!is_string($lang)) {
            throw new InvalidArgumentException(
                'The language to render as must be a string.'
            );
        }

        $origLang = $this->translator()->getLocale();
        $this->translator()->setLocale($lang);

        if (strpos($pattern, '{{') === false) {
            $output = (string)$obj[$pattern];
        } elseif (($obj instanceof ViewableInterface) && $obj->view()) {
            $output = $obj->renderTemplate($pattern);
        } else {
            $callback = function($matches) use ($obj) {
                $prop = trim($matches[1]);
                return (string)$obj[$prop];
            };

            $output = preg_replace_callback('~\{\{\s*(.*?)\s*\}\}~i', $callback, $pattern);
        }

        $this->translator()->setLocale($origLang);

        return $output;
    }

    /**
     * Retrieve an object by its ID.
     *
     * Loads the object from the cache store or from the storage source.
     *
     * @param  mixed $objId Object id.
     * @return ModelInterface
     */
    protected function loadObject($objId)
    {
        if ($objId instanceof ModelInterface) {
            return $objId;
        }

        $obj = $this->modelLoader()->load($objId);
        if (!$obj->id()) {
            return null;
        } else {
            return $obj;
        }
    }

    /**
     * Retrieve the model loader.
     *
     * @param  string $objType The object type.
     * @throws InvalidArgumentException If the object type is invalid.
     * @return ModelLoader
     */
    protected function modelLoader($objType = null)
    {
        if ($objType === null) {
            $objType = $this->objType();
        } elseif (!is_string($objType)) {
            throw new InvalidArgumentException(
                'Object type must be a string.'
            );
        }

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
