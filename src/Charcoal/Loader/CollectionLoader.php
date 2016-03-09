<?php

namespace Charcoal\Loader;

use \InvalidArgumentException;
use \Exception;
use \PDO;

// Dependencies from PSR-3 (Logger)
use \Psr\Log\LoggerAwareInterface;
use \Psr\Log\LoggerAwareTrait;
use \Psr\Log\NullLogger;

// Intra-module (`charcoal-core`) dependencies
use \Charcoal\Model\ModelInterface;
use \Charcoal\Model\Collection;

// Local Dependencies
use \Charcoal\Source\Database\DatabaseFilter;
use \Charcoal\Source\Database\DatabaseOrder;
use \Charcoal\Source\Database\DatabasePagination;

/**
 * Object Collection Loader
 */
class CollectionLoader implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * The source to load objects from.
     *
     * @var SourceInterface $source
     */
    private $source;

    /**
     * The model to load the collection from.
     *
     * @var ModelInterface $model
     */
    private $model;

    /**
     * The constructor arguments for the model.
     *
     * @var array $arguments
     */
    private $arguments;

    /**
     * The callback routine applied to every object added to the collection.
     *
     * @var callable $callback
     */
    private $callback;

    /**
     * Return a new CollectionLoader object.
     *
     * @param array $data The loader's dependencies.
     */
    public function __construct($data)
    {
        if (!isset($data['logger'])) {
            $data['logger'] = new NullLogger();
        }
        $this->setLogger($data['logger']);
        $this->setArguments([ 'logger' => $this->logger ]);
    }

    /**
     * Set the loader data, from an associative array map (or any other Traversable).
     *
     * @param  array|Traversable $data Data to assign to the loader.
     * @return CollectionLoader Chainable
     */
    public function setData($data)
    {
        if (isset($data['filters'])) {
            $this->setFilters($data['filters']);
        }
        foreach ($data as $key => $val) {
            $setter = $this->setter($key);

            if (is_callable([$this, $setter])) {
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
     * @throws Exception If no source has been defined.
     * @return mixed
     */
    public function source()
    {
        if (!isset($this->source)) {
            throw new Exception('No source set.');
        }

        return $this->source;
    }

    /**
     * Set the source to load objects from.
     *
     * @param  mixed $source A data source.
     * @return CollectionLoader Chainable
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Retrieve the object model.
     *
     * @throws Exception If no model has been defined.
     * @return Model
     */
    public function model()
    {
        if (!isset($this->model)) {
            throw new Exception('No model set.');
        }

        return $this->model;
    }

    /**
     * Set the model to use for the loaded objects.
     *
     * @param  ModelInterface $model An object model.
     * @return Source Chainable
     */
    public function setModel(ModelInterface $model)
    {
        $this->model = $model;
        $this->setSource($model->source());

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
     * @return ColelectionLoader Chainable
     */
    public function setProperties($properties)
    {
        $this->source()->setProperties($properties);

        return $this;
    }

    /**
     * Alias of {@see SourceInterface::addProperty()}
     *
     * @param  string $property A property identifier.
     * @return CollectionLoader Chainable
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
     * @return CollectionLoader Chainable
     */
    public function setKeywords()
    {
        foreach ($keywords as $k) {
            $keyword = $k[0];
            $properties = (isset($k[1]) ? $k[1] : null);
            $this->addKeyword($keyword, $properties);
        }

        return $this;
    }

    /**
     * Add a "search" keyword filter to multiple properties.
     *
     * @param  string $keyword    A value to match among $properties.
     * @param  array  $properties An array of property identifiers.
     * @return CollectionLoader Chainable
     */
    public function addKeyword($keyword, $properties = null)
    {
        $model = $this->model();
        if (!is_array($properties) || empty($properties)) {
            // @todo Load from
            $properties = [];
        }

        foreach ($properties as $property_ident) {
            $prop = $model->p($property_ident);
            $val = ('%'.$keyword.'%');
            $this->addFilter([
                'property' => $property_ident,
                'val'      => $val,
                'operator' => 'LIKE',
                'operand'  => 'OR'
            ]);
        }

        return $this;
    }

    /**
     * Alias of {@see SourceInterface::filters()}
     *
     * @return array
     */
    public function filters()
    {
        return $this->source()->filters();
    }

    /**
     * Alias of {@see SourceInterface::setFilters()}
     *
     * @param  array $filters An array of filters.
     * @return Collection Chainable
     */
    public function setFilters($filters)
    {
        $this->source()->setFilters($filters);

        return $this;
    }

    /**
     * Alias of {@see SourceInterface::addFilter()}
     *
     * @param  string|array|Filter $param   A property identifier, filter array, or Filter object.
     * @param  mixed               $val     Optional. The value to match. Only used if the first argument is a string.
     * @param  array               $options Optional. Filter options. Only used if the first argument is a string.
     * @return CollectionLoader Chainable
     */
    public function addFilter($param, $val = null, array $options = null)
    {
        $this->source()->addFilter($param, $val, $options);

        return $this;
    }

    /**
     * Alias of {@see SourceInterface::orders()}
     *
     * @return array
     */
    public function orders()
    {
        return $this->source()->orders();
    }

    /**
     * Alias of {@see SourceInterface::setOrders()}
     *
     * @param  array $orders An array of orders.
     * @return CollectionLoader Chainable
     */
    public function setOrders($orders)
    {
        $this->source()->setOrders($orders);

        return $this;
    }

    /**
     * Alias of {@see SourceInterface::addOrder()}
     *
     * @param  string|array|Order $param        A property identifier, order array, or Order object.
     * @param  string             $mode         Optional. Sort order. Only used if the first argument is a string.
     * @param  array              $orderOptions Optional. Filter options. Only used if the first argument is a string.
     * @return CollectionLoader Chainable
     */
    public function addOrder($param, $mode = 'asc', $orderOptions = null)
    {
        $this->source()->addOrder($param, $mode, $orderOptions);

        return $this;
    }

    /**
     * Alias of {@see SourceInterface::pagination()}
     *
     * @return Pagination
     */
    public function pagination()
    {
        return $this->source()->pagination();
    }

    /**
     * Alias of {@see SourceInterface::setPagination()}
     *
     * @param  mixed $param An associative array of pagination settings.
     * @return CollectionLoader Chainable
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
     * @return CollectionLoader Chainable
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
     * @return CollectionLoader Chainable
     */
    public function setNumPerPage($num)
    {
        $this->pagination()->setNumPerPage($num);

        return $this;
    }

    /**
     * Retrieve the model's constructor arguments.
     *
     * @return array
     */
    public function arguments()
    {
        return $this->arguments;
    }

    /**
     * Set the model's constructor arguments.
     *
     * @param array $arguments The constructor arguments to be passed to the created object's initialization.
     * @return CollectionLoader Chainable
     */
    public function setArguments(array $arguments)
    {
        $this->arguments = $arguments;

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
     * Set the callback routine applied to every object added to the collection.
     *
     * @param callable $callback The callback routine.
     * @return CollectionLoader Chainable
     */
    public function setCallback(callable $callback)
    {
        $this->callback = $callback;

        return $this;
    }

    /**
     * Load a collection from source.
     *
     * @param  string|null $ident  Optional. A pre-defined list to use from the model.
     * @param  array       $args   Optional. The constructor arguments.
     *                             Leave blank to use `$arguments` member.
     * @param  callable    $cb     Optional. Apply a callback to every entity of the collection.
     *                             Leave blank to use `$callback` member.
     * @throws Exception If the database connection fails.
     * @return Collection
     */
    public function load($ident = null, array $args = null, callable $cb = null)
    {
        // Unused.
        unset($ident);

        $db = $this->source()->db();

        if (!$db) {
            throw new Exception('Could not instanciate a database connection.');
        }

        if (!isset($args)) {
            $args = $this->arguments();
        }

        if (!isset($cb)) {
            $cb = $this->callback();
        }

        /** @todo Filters, pagination, select, etc */
        $query = $this->source()->sqlLoad();
        $this->logger->debug($query);
        $collection = new Collection();

        $sth = $db->prepare($query);
        /** @todo Filter binds */
        $sth->execute();
        $sth->setFetchMode(PDO::FETCH_ASSOC);

        $classname = get_class($this->model());

        while ($objData = $sth->fetch()) {
            $obj = new $classname($args);
            $obj->setFlatData($objData);

            if (isset($cb)) {
                call_user_func_array($cb, [ &$obj ]);
            }

            $collection->add($obj);
        }

        return $collection;
    }

    /**
     * Allow an object to define how the key getter are called.
     *
     * @param string $key  The key to get the getter from.
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
     * @param string $key  The key to get the setter from.
     * @return string The setter method name, for a given key.
     */
    protected function setter($key)
    {
        $setter = 'set_'.$key;
        return $this->camelize($setter);
    }

    /**
     * Transform a snake_case string to camelCase.
     *
     * @param string $str The snake_case string to camelize.
     * @return string The camelcase'd string.
     */
    protected function camelize($str)
    {
        return lcfirst(implode('', array_map('ucfirst', explode('_', $str))));
    }
}
