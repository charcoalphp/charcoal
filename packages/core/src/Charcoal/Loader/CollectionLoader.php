<?php

namespace Charcoal\Loader;

use InvalidArgumentException;
use RuntimeException;
use ArrayAccess;
use Traversable;
use PDO;
// From PSR-3
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;
// From 'charcoal-core'
use Charcoal\Model\ModelInterface;
use Charcoal\Model\Collection;
use Charcoal\Source\FilterCollectionInterface;
use Charcoal\Source\OrderCollectionInterface;
use Charcoal\Source\FilterInterface;
use Charcoal\Source\OrderInterface;
use Charcoal\Source\PaginationInterface;
use Charcoal\Source\SourceInterface;

/**
 * Object Collection Loader
 */
class CollectionLoader implements
    FilterCollectionInterface,
    OrderCollectionInterface,
    LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * The source to load objects from.
     *
     * @var SourceInterface
     */
    private $source;

    /**
     * The model to load the collection from.
     *
     * @var ModelInterface
     */
    private $model;

    /**
     * Store the factory instance for the current class.
     *
     * @var FactoryInterface
     */
    private $factory;

    /**
     * The callback routine applied to every object added to the collection.
     *
     * @var callable|null
     */
    private $callback;

    /**
     * The field which defines the data's model.
     *
     * @var string|null
     */
    private $dynamicTypeField;

    /**
     * The class name of the collection to use.
     *
     * Must be a fully-qualified PHP namespace and an implementation of {@see ArrayAccess}.
     *
     * @var string
     */
    private $collectionClass = Collection::class;

    /**
     * Return a new CollectionLoader object.
     *
     * @param array $data The loader's dependencies.
     */
    public function __construct(array $data)
    {
        if (!isset($data['logger'])) {
            $data['logger'] = new NullLogger();
        }

        $this->setLogger($data['logger']);

        if (isset($data['collection'])) {
            $this->setCollectionClass($data['collection']);
        }

        if (isset($data['factory'])) {
            $this->setFactory($data['factory']);
        }

        if (isset($data['model'])) {
            $this->setModel($data['model']);
        }
    }

    /**
     * Set an object model factory.
     *
     * @param  FactoryInterface $factory The model factory, to create objects.
     * @return self
     */
    public function setFactory(FactoryInterface $factory)
    {
        $this->factory = $factory;

        return $this;
    }

    /**
     * Retrieve the object model factory.
     *
     * @throws RuntimeException If the model factory was not previously set.
     * @return FactoryInterface
     */
    protected function factory()
    {
        if ($this->factory === null) {
            throw new RuntimeException(
                sprintf('Model Factory is not defined for "%s"', get_class($this))
            );
        }

        return $this->factory;
    }

    /**
     * Create a new model.
     *
     * @return ModelInterface
     */
    public function createModel()
    {
        $obj = $this->factory()->create($this->modelClass());
        return $obj;
    }

    /**
     * Create a new model from a dataset.
     *
     * @param  array $data The model data.
     * @return ModelInterface
     */
    protected function createModelFromData(array $data)
    {
        $obj = $this->factory()->create($this->dynamicModelClass($data));
        return $obj;
    }

    /**
     * Set the loader settings.
     *
     * @param  array $data Data to assign to the loader.
     * @return self
     */
    public function setData(array $data)
    {
        foreach ($data as $key => $val) {
            $setter = $this->setter($key);
            if (is_callable([ $this, $setter ])) {
                $this->{$setter}($val);
            } else {
                $this->{$key} = $val;
            }
        }

        return $this;
    }

    /**
     * Retrieve the source to load objects from.
     *
     * @throws RuntimeException If no source has been defined.
     * @return SourceInterface
     */
    public function source()
    {
        if ($this->source === null) {
            throw new RuntimeException('No source set.');
        }

        return $this->source;
    }

    /**
     * Set the source to load objects from.
     *
     * @param  SourceInterface $source A data source.
     * @return self
     */
    public function setSource(SourceInterface $source)
    {
        $source->reset();

        $this->source = $source;

        return $this;
    }

    /**
     * Reset everything but the model.
     *
     * @return self
     */
    public function reset()
    {
        if ($this->source) {
            $this->source()->reset();
        }

        $this->callback = null;
        $this->dynamicTypeField = null;

        return $this;
    }

    /**
     * Retrieve the object model.
     *
     * @throws RuntimeException If no model has been defined.
     * @return ModelInterface
     */
    public function model()
    {
        if ($this->model === null) {
            throw new RuntimeException('The collection loader must have a model.');
        }

        return $this->model;
    }

    /**
     * Determine if the loader has an object model.
     *
     * @return boolean
     */
    public function hasModel()
    {
        return !!$this->model;
    }

    /**
     * Set the model to use for the loaded objects.
     *
     * @param  string|ModelInterface $model An object model.
     * @throws InvalidArgumentException If the given argument is not a model.
     * @return self
     */
    public function setModel($model)
    {
        if (is_string($model)) {
            $model = $this->factory()->get($model);
        }

        if (!$model instanceof ModelInterface) {
            throw new InvalidArgumentException(
                sprintf(
                    'The model must be an instance of "%s"',
                    ModelInterface::class
                )
            );
        }

        $this->model = $model;

        $this->setSource($model->source());

        return $this;
    }

    /**
     * Retrieve the model class.
     *
     * @return string
     */
    public function modelClass()
    {
        return get_class($this->model());
    }

    /**
     * Retrieve the model class.
     *
     * @param  array $data The model data.
     * @return string
     */
    protected function dynamicModelClass(array $data)
    {
        $field = $this->dynamicTypeField();
        if ($field && isset($data[$field])) {
            return $data[$field];
        }

        return $this->modelClass();
    }

    /**
     * @return string|null
     */
    public function dynamicTypeField()
    {
        return $this->dynamicTypeField;
    }

    /**
     * Determine if the model has a dynamic object type.
     *
     * @return boolean
     */
    public function hasDynamicTypeField()
    {
        return !!$this->dynamicTypeField;
    }

    /**
     * @param  string $field The field to use for dynamic object type.
     * @throws InvalidArgumentException If the field is not a string.
     * @return self
     */
    public function setDynamicTypeField($field)
    {
        if (!is_string($field)) {
            throw new InvalidArgumentException(
                'Dynamic type field must be a string'
            );
        }

        $this->dynamicTypeField = $field;

        return $this;
    }

    /**
     * Alias of {@see SourceInterface::properties()}
     *
     * @return array
     */
    public function properties()
    {
        return $this->source()->properties();
    }

    /**
     * Alias of {@see SourceInterface::setProperties()}
     *
     * @param  array $properties An array of property identifiers.
     * @return self
     */
    public function setProperties(array $properties)
    {
        $this->source()->setProperties($properties);

        return $this;
    }

    /**
     * Alias of {@see SourceInterface::addProperty()}
     *
     * @param  string $property A property identifier.
     * @return self
     */
    public function addProperty($property)
    {
        $this->source()->addProperty($property);

        return $this;
    }

    /**
     * Set "search" keywords to filter multiple properties.
     *
     * @param  array $keywords An array of keywords and properties.
     *     Expected format: `[ "search query", [ "field names…" ] ]`.
     * @return self
     */
    public function setKeywords(array $keywords)
    {
        foreach ($keywords as $query) {
            $keyword    = $query[0];
            $properties = (isset($query[1]) ? (array)$query[1] : null);
            $this->addKeyword($keyword, $properties);
        }

        return $this;
    }

    /**
     * Add a "search" keyword filter to multiple properties.
     *
     * @param  string $keyword    A value to match among $properties.
     * @param  array  $properties One or more of properties to search amongst.
     * @return self
     */
    public function addKeyword($keyword, array $properties = null)
    {
        if ($properties === null) {
            $properties = [];
        }

        foreach ($properties as $propertyIdent) {
            $val = ('%' . $keyword . '%');
            $this->addFilter([
                'property' => $propertyIdent,
                'operator' => 'LIKE',
                'value'    => $val,
                'operand'  => 'OR'
            ]);
        }

        return $this;
    }

    /**
     * Alias of {@see SourceInterface::filters()}
     *
     * @return FilterInterface[]
     */
    public function filters()
    {
        return $this->source()->filters();
    }

    /**
     * Alias of {@see SourceInterface::hasFilters()}
     *
     * @return boolean
     */
    public function hasFilters()
    {
        return $this->source()->hasFilters();
    }

    /**
     * Alias of {@see SourceInterface::setFilters()}
     *
     * @param  array $filters An array of filters.
     * @return self
     */
    public function setFilters(array $filters)
    {
        $this->source()->setFilters($filters);
        return $this;
    }

    /**
     * Alias of {@see SourceInterface::addFilters()}
     *
     * @param  array $filters An array of filters.
     * @return self
     */
    public function addFilters(array $filters)
    {
        foreach ($filters as $f) {
            $this->addFilter($f);
        }
        return $this;
    }

    /**
     * Alias of {@see SourceInterface::addFilter()}
     *
     * @param  mixed $param   The property to filter by,
     *     a {@see FilterInterface} object,
     *     or a filter array structure.
     * @param  mixed $value   Optional value for the property to compare against.
     *     Only used if the first argument is a string.
     * @param  array $options Optional extra settings to apply on the filter.
     * @return self
     */
    public function addFilter($param, $value = null, array $options = null)
    {
        $this->source()->addFilter($param, $value, $options);
        return $this;
    }

    /**
     * Alias of {@see SourceInterface::orders()}
     *
     * @return OrderInterface[]
     */
    public function orders()
    {
        return $this->source()->orders();
    }

    /**
     * Alias of {@see SourceInterface::hasOrders()}
     *
     * @return boolean
     */
    public function hasOrders()
    {
        return $this->source()->hasOrders();
    }

    /**
     * Alias of {@see SourceInterface::setOrders()}
     *
     * @param  array $orders An array of orders.
     * @return self
     */
    public function setOrders(array $orders)
    {
        $this->source()->setOrders($orders);
        return $this;
    }

    /**
     * Alias of {@see SourceInterface::addOrders()}
     *
     * @param  array $orders An array of orders.
     * @return self
     */
    public function addOrders(array $orders)
    {
        foreach ($orders as $o) {
            $this->addOrder($o);
        }
        return $this;
    }

    /**
     * Alias of {@see SourceInterface::addOrder()}
     *
     * @param  mixed  $param   The property to sort by,
     *     a {@see OrderInterface} object,
     *     or a order array structure.
     * @param  string $mode    Optional sorting mode.
     *     Defaults to ascending if a property is provided.
     * @param  array  $options Optional extra settings to apply on the order.
     * @return self
     */
    public function addOrder($param, $mode = 'asc', array $options = null)
    {
        $this->source()->addOrder($param, $mode, $options);
        return $this;
    }

    /**
     * Alias of {@see SourceInterface::pagination()}
     *
     * @return PaginationInterface
     */
    public function pagination()
    {
        return $this->source()->pagination();
    }

    /**
     * Alias of {@see SourceInterface::setPagination()}
     *
     * @param  mixed $param An associative array of pagination settings.
     * @return self
     */
    public function setPagination($param)
    {
        $this->source()->setPagination($param);

        return $this;
    }

    /**
     * Alias of {@see PaginationInterface::page()}
     *
     * @return integer
     */
    public function page()
    {
        return $this->pagination()->page();
    }

    /**
     * Alias of {@see PaginationInterface::pagination()}
     *
     * @param  integer $page A page number.
     * @return self
     */
    public function setPage($page)
    {
        $this->pagination()->setPage($page);

        return $this;
    }

    /**
     * Alias of {@see PaginationInterface::numPerPage()}
     *
     * @return integer
     */
    public function numPerPage()
    {
        return $this->pagination()->numPerPage();
    }

    /**
     * Alias of {@see PaginationInterface::setNumPerPage()}
     *
     * @param  integer $num The number of items to display per page.
     * @return self
     */
    public function setNumPerPage($num)
    {
        $this->pagination()->setNumPerPage($num);

        return $this;
    }

    /**
     * Set the callback routine applied to every object added to the collection.
     *
     * @param  callable $callback The callback routine.
     * @return self
     */
    public function setCallback(callable $callback)
    {
        $this->callback = $callback;

        return $this;
    }

    /**
     * Retrieve the callback routine applied to every object added to the collection.
     *
     * @return callable|null
     */
    public function callback()
    {
        return $this->callback;
    }

    /**
     * Load a collection from source.
     *
     * @param  string|null   $ident    Optional. A pre-defined list to use from the model.
     * @param  callable|null $callback Process each entity after applying raw data.
     *    Leave blank to use {@see CollectionLoader::callback()}.
     * @param  callable|null $before   Process each entity before applying raw data.
     * @throws Exception If the database connection fails.
     * @return ModelInterface[]|ArrayAccess
     */
    public function load($ident = null, callable $callback = null, callable $before = null)
    {
        // Unused.
        unset($ident);

        $query = $this->source()->sqlLoad();

        return $this->loadFromQuery($query, $callback, $before);
    }

    /**
     * Get the total number of items for this collection query.
     *
     * @throws RuntimeException If the database connection fails.
     * @return integer
     */
    public function loadCount()
    {
        $query = $this->source()->sqlLoadCount();

        $db = $this->source()->db();
        if (!$db) {
            throw new RuntimeException(
                'Could not instanciate a database connection.'
            );
        }
        $this->logger->debug($query);

        $sth = $db->prepare($query);
        $sth->execute();
        $res = $sth->fetchColumn(0);

        return (int)$res;
    }

    /**
     * Load list from query.
     *
     * **Example — Binding values to $query**
     *
     * ```php
     * $this->loadFromQuery([
     *     'SELECT name, colour, calories FROM fruit WHERE calories < :calories AND colour = :colour',
     *     [
     *         'calories' => 150,
     *         'colour'   => 'red'
     *     ],
     *     [ 'calories' => PDO::PARAM_INT ]
     * ]);
     * ```
     *
     * @param  string|array  $query    The SQL query as a string or an array composed of the query,
     *     parameter binds, and types of parameter bindings.
     * @param  callable|null $callback Process each entity after applying raw data.
     *    Leave blank to use {@see CollectionLoader::callback()}.
     * @param  callable|null $before   Process each entity before applying raw data.
     * @throws RuntimeException If the database connection fails.
     * @throws InvalidArgumentException If the SQL string/set is invalid.
     * @return ModelInterface[]|ArrayAccess
     */
    public function loadFromQuery($query, callable $callback = null, callable $before = null)
    {
        $db = $this->source()->db();

        if (!$db) {
            throw new RuntimeException(
                'Could not instanciate a database connection.'
            );
        }

        /** @todo Filter binds */
        if (is_string($query)) {
            $this->logger->debug($query);
            $sth = $db->prepare($query);
            $sth->execute();
        } elseif (is_array($query)) {
            list($query, $binds, $types) = array_pad($query, 3, []);
            $sth = $this->source()->dbQuery($query, $binds, $types);
        } else {
            throw new InvalidArgumentException(sprintf(
                'The SQL query must be a string or an array: ' .
                '[ string $query, array $binds, array $dataTypes ]; ' .
                'received %s',
                is_object($query) ? get_class($query) : $query
            ));
        }

        $sth->setFetchMode(PDO::FETCH_ASSOC);

        if ($callback === null) {
            $callback = $this->callback();
        }

        return $this->processCollection($sth, $before, $callback);
    }

    /**
     * Process the collection of raw data.
     *
     * @param  mixed[]|Traversable $results The raw result set.
     * @param  callable|null       $before  Process each entity before applying raw data.
     * @param  callable|null       $after   Process each entity after applying raw data.
     * @return ModelInterface[]|ArrayAccess
     */
    protected function processCollection($results, callable $before = null, callable $after = null)
    {
        $collection = $this->createCollection();
        foreach ($results as $objData) {
            $obj = $this->processModel($objData, $before, $after);

            if ($obj instanceof ModelInterface) {
                $collection[] = $obj;
            }
        }

        return $collection;
    }

    /**
     * Process the raw data for one model.
     *
     * @param  mixed         $objData The raw dataset.
     * @param  callable|null $before  Process each entity before applying raw data.
     * @param  callable|null $after   Process each entity after applying raw data.
     * @return ModelInterface|ArrayAccess|null
     */
    protected function processModel($objData, callable $before = null, callable $after = null)
    {
        $obj = $this->createModelFromData($objData);

        if ($before !== null) {
            call_user_func_array($before, [ &$obj ]);
        }

        $obj->setFlatData($objData);

        if ($after !== null) {
            call_user_func_array($after, [ &$obj ]);
        }

        return $obj;
    }

    /**
     * Create a collection class or array.
     *
     * @throws RuntimeException If the collection class is invalid.
     * @return array|ArrayAccess
     */
    public function createCollection()
    {
        $collectClass = $this->collectionClass();
        if ($collectClass === 'array') {
            return [];
        }

        if (!class_exists($collectClass)) {
            throw new RuntimeException(sprintf(
                'Collection class [%s] does not exist.',
                $collectClass
            ));
        }

        if (!is_subclass_of($collectClass, ArrayAccess::class)) {
            throw new RuntimeException(sprintf(
                'Collection class [%s] must implement ArrayAccess.',
                $collectClass
            ));
        }

        $collection = new $collectClass();

        return $collection;
    }

    /**
     * Set the class name of the collection.
     *
     * @param  string $className The class name of the collection.
     * @throws InvalidArgumentException If the class name is not a string.
     * @return self
     */
    public function setCollectionClass($className)
    {
        if (!is_string($className)) {
            throw new InvalidArgumentException(
                'Collection class name must be a string.'
            );
        }

        $this->collectionClass = $className;

        return $this;
    }

    /**
     * Retrieve the class name of the collection.
     *
     * @return string
     */
    public function collectionClass()
    {
        return $this->collectionClass;
    }

    /**
     * Allow an object to define how the key getter are called.
     *
     * @param  string $key The key to get the getter from.
     * @return string The getter method name, for a given key.
     */
    protected function getter($key)
    {
        $getter = $key;
        return $this->camelize($getter);
    }

    /**
     * Allow an object to define how the key setter are called.
     *
     * @param  string $key The key to get the setter from.
     * @return string The setter method name, for a given key.
     */
    protected function setter($key)
    {
        $setter = 'set_' . $key;
        return $this->camelize($setter);
    }

    /**
     * Transform a snake_case string to camelCase.
     *
     * @param  string $str The snake_case string to camelize.
     * @return string The camelcase'd string.
     */
    protected function camelize($str)
    {
        return lcfirst(implode('', array_map('ucfirst', explode('_', $str))));
    }
}
