<?php

namespace Charcoal\Loader;

use \InvalidArgumentException;
use \Exception;
use \PDO;

// Dependencies from PSR-3 (Logger)
use \Psr\Log\LoggerAwareInterface;
use \Psr\Log\LoggerAwareTrait;

// Intra-module (`charcoal-core`) dependencies
use \Charcoal\Model\ModelInterface;
use \Charcoal\Model\Collection;

// Local Dependencies
use \Charcoal\Source\Database\DatabaseFilter;
use \Charcoal\Source\Database\DatabaseOrder;
use \Charcoal\Source\Database\DatabasePagination;

/**
 * Collection Loader
 *
 * @uses \Charcoal\Model\Collection
 */
class CollectionLoader implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var array $properties
     */
    private $properties = [];

    /**
     * @var array $propertiesOptions
     */
    private $propertiesOptions = [];

    /**
     * Array of `Filter` objects.
     *
     * @var array $filters
     */
    private $filters = [];

    /**
     * Array of `Order` object.
     *
     * @var array $orders
     */
    private $orders = [];

    /**
     * The `Pagniation` object.
     *
     * @var Pagination|null $pagination
     */
    private $pagination;

    /**
     * The source to load the object from.
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
     * Return a new Collection loader.
     *
     * @param array $data The loader's dependencies.
     */
    public function __construct($data)
    {
        if (!isset($data['logger'])) {
            $data['logger'] = new \Psr\Log\NullLogger();
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
        foreach ($data as $key=> $val) {
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
     * @param mixed $source
     * @return CollectionLoader Chainable
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * @throws Exception
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
     * @param ModelInterface $model
     * @return Source Chainable
     */
    public function setModel(ModelInterface $model)
    {
        $this->model = $model;
        $this->setSource($model->source());

        return $this;
    }

    /**
     * @throws Exception if not model was previously set
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
     * @param array $properties
     * @throws InvalidArgumentException
     * @return ColelectionLoader Chainable
     */
    public function setProperties($properties)
    {
        return $this->source()->setProperties($properties);
    }

    /**
     * @return array
     */
    public function properties()
    {
        return $this->source()->properties();
    }

    /**
     * @param string $property Property ident
     * @throws InvalidArgumentException if property is not a string or empty
     * @return CollectionLoader Chainable
     */
    public function addProperty($property)
    {
        return $this->source()->addProperty($property);
    }

    /**
     * @param array $keywords
     * @return CollectionLoader Chainable
     */
    public function setKeywords()
    {
        foreach ($keywords as $k) {
            $keyword = $k[0];
            $properties = isset($k[1]) ? $k[1] : null;
            $this->addKeyword($keyword, $properties);
        }
        return $this;
    }

    /**
     * Helper function to add a "search" keyword filter to multiple properties.
     *
     * @param string $keyword
     * @param array $properties
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
                'property'  => $property_ident,
                'val'       => $val,
                'operator'  => 'LIKE',
                'operand'   => 'OR'
            ]);
        }

        return $this;
    }

    /**
     * @param array $filters
     * @throws InvalidArgumentException
     * @return Collection Chainable
     */
    public function setFilters($filters)
    {
        return $this->source()->setFilters($filters);
    }

    /**
     * @return array
     */
    public function filters()
    {
        return $this->source()->filters();
    }

    /**
     * Add a collection filter to the loader.
     *
     * There are 3 different ways of adding a filter:
     * - as a `Filter` object, in which case it will be added directly.
     *   - `addFilter($obj);`
     * - as an array of options, which will be used to build the `Filter` object
     *   - `addFilter(['property' => 'foo', 'val' => 42, 'operator' => '<=']);`
     * - as 3 parameters: `property`, `val` and `options`
     *   - `addFilter('foo', 42, ['operator' => '<=']);`
     *
     * @param string|array|Filter $param
     * @param mixed               $val     Optional: Only used if the first argument is a string
     * @param array               $options Optional: Only used if the first argument is a string
     * @throws InvalidArgumentException if property is not a string or empty
     * @return CollectionLoader (Chainable)
     */
    public function addFilter($param, $val = null, array $options = null)
    {
        return $this->source()->addFilter($param, $val, $options);
    }

    /**
     * @param array $orders
     * @return CollectionLoader Chainable
     */
    public function setOrders($orders)
    {
        return $this->source()->setOrders($orders);
    }

    /**
     * @return array
     */
    public function orders()
    {
        return $this->source()->orders();
    }

    /**
     * @param string|array|Order $param
     * @param string             $mode          Optional
     * @param array              $orderOptions Optional
     * @throws InvalidArgumentException
     * @return CollectionLoader Chainable
     */
    public function addOrder($param, $mode = 'asc', $orderOptions = null)
    {
        return $this->source()->addOrder($param, $mode, $orderOptions);
    }

    /**
     * @param mixed $param
     * @return CollectionLoader Chainable
     */
    public function setPagination($param)
    {
        return $this->source()->setPagination($param);
    }

    /**
     * @return Pagination
     */
    public function pagination()
    {
        return $this->source()->pagination();
    }

    /**
     * @param integer $page
     * @throws InvalidArgumentException
     * @return CollectionLoader Chainable
     */
    public function setPage($page)
    {
        if (!is_integer($page)) {
            throw new InvalidArgumentException('Page must be an integer.');
        }
        $this->pagination()->setPage($page);
        return $this;
    }

    /**
     * @return integer
     */
    public function page()
    {
        return $this->pagination()->page();
    }

    /**
     * @return integer
     */
    public function numPerPage()
    {
        return $this->pagination()->numPerPage();
    }

    /**
     * @param integer $num
     * @throws InvalidArgumentException
     * @return CollectionLoader Chainable
     */
    public function setNumPerPage($num)
    {
        if (!is_integer($num)) {
            throw new InvalidArgumentException('Num must be an integer.');
        }
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
     * @param  array       $args   Optional. The constructor arguments. Leave blank to use `$arguments` member.
     * @param  callable    $cb     Optional. Apply a callback to every entity of the collection. Leave blank to use `$callback` member.
     * @throws Exception If the database connection fails.
     * @return Collection
     */
    public function load($ident = null, array $args = null, callable $cb = null)
    {
        // Unused.
        unset($ident);

        if (!isset($args)) {
            $args = $this->arguments();
        }

        if (!isset($cb)) {
            $cb = $this->callback();
        }

        $db = $this->source()->db();
        if (!$db) {
            throw new Exception('Could not instanciate a database connection.');
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
