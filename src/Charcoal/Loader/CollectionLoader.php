<?php

namespace Charcoal\Loader;

// Intra-module (`charcoal-core`) dependencies
use \Exception;
use \InvalidArgumentException;

use \PDO;

// Intra-module (`charcoal-core`) dependencies
use \Charcoal\Charcoal;
use \Charcoal\Model\ModelInterface;
use \Charcoal\Model\Collection;

// Local namespace dependencies
use \Charcoal\Source\Database\DatabaseFilter as Filter;
use \Charcoal\Source\Database\DatabaseOrder as Order;
use \Charcoal\Source\Database\DatabasePagination as Pagination;

/**
* Collection Loader
*/
class CollectionLoader extends AbstractLoader
{
    /**
    * @var array $_properties
    */
    private $_properties = [];
    /**
    * @var array $_properties_options
    */
    private $_properties_options = [];
    /**
    * Array of `Filter` objects
    * @var array $_filters
    */
    private $_filters = [];
    /**
    * Array of `Order` object
    * @var array $_orders
    */
    private $_orders = [];
    /**
    * The `Pagniation` object
    * @var Pagination|null $_pagination
    */
    private $_pagination = null;

    /**
    * The source to load the object from
    * @var SourceInterface $_source
    */
    private $_source = null;
    /**
    * The model to load the collection from
    * @var ModelInterface $_model
    */
    private $_model = null;

    /**
    * @param array $data
    * @return CollectionLoader Chainable
    */
    public function set_data(array $data)
    {
        if (isset($data['properties'])) {
            $this->set_properties($data['properties']);
        }
        if (isset($data['filters'])) {
            $this->set_filters($data['filters']);
        }
        if (isset($data['orders'])) {
            $this->set_orders($data['orders']);
        }
        if (isset($data['pagination'])) {
            $this->set_pagination($data['pagination']);
        }
        return $this;
    }

    /**
    * @param mixed $source
    * @return CollectionLoader Chainable
    */
    public function set_source($source)
    {
        $this->_source = $source;
        return $this;
    }

    /**
    * @throws Exception
    * @return mixed
    */
    public function source()
    {
        if ($this->_source === null) {
            throw new Exception('No source set.');
        }
        return $this->_source;
    }

    /**
    * @param ModelInterface $model
    * @return Source Chainable
    */
    public function set_model(ModelInterface $model)
    {
        $this->_model = $model;
        $this->set_source($model->source());
        return $this;
    }

    /**
    * @throws Exception if not model was previously set
    * @return Model
    */
    public function model()
    {
        if ($this->_model === null) {
            throw new Exception('No model set.');
        }
        return $this->_model;
    }

    /**
    * @param array $properties
    * @throws InvalidArgumentException
    * @return ColelectionLoader Chainable
    */
    public function set_properties($properties)
    {
        return $this->source()->set_properties($properties);
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
    public function add_property($property)
    {
        return $this->source()->add_property($property);
    }

    /**
    * @param array $filters
    * @throws InvalidArgumentException
    * @return Collection Chainable
    */
    public function set_filters($filters)
    {
        return $this->source()->set_filters($filters);
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
    *   - `add_filter($obj);`
    * - as an array of options, which will be used to build the `Filter` object
    *   - `add_filter(['property' => 'foo', 'val' => 42, 'operator' => '<=']);`
    * - as 3 parameters: `property`, `val` and `options`
    *   - `add_filter('foo', 42, ['operator' => '<=']);`
    *
    * @param string|array|Filter $param
    * @param mixed               $val     Optional: Only used if the first argument is a string
    * @param array               $options Optional: Only used if the first argument is a string
    * @throws InvalidArgumentException if property is not a string or empty
    * @return CollectionLoader (Chainable)
    */
    public function add_filter($param, $val = null, array $options = null)
    {
        return $this->source()->add_filter($param, $val, $options);
    }

    /**
    * @param array $orders
    * @return CollectionLoader Chainable
    */
    public function set_orders($orders)
    {
        return $this->set_orders($orders);
    }

    /**
    * @return array
    */
    public function orders()
    {
        return $this->orders();
    }

    /**
    * @param string|array|Order $param
    * @param string             $mode          Optional
    * @param array              $order_options Optional
    * @throws InvalidArgumentException
    * @return CollectionLoader Chainable
    */
    public function add_order($param, $mode = 'asc', $order_options = null)
    {
        return $this->source()->add_order($param, $mode, $order_options);
    }

    /**
    * @param mixed $param
    * @return CollectionLoader Chainable
    */
    public function set_pagination($param)
    {
        return $this->source()->set_pagination($param);
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
    public function set_page($page)
    {
        if (!is_integer($page)) {
            throw new InvalidArgumentException('Page must be an integer.');
        }
        $this->pagination()->set_page($page);
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
    * @param integer $num
    * @throws InvalidArgumentException
    * @return CollectionLoader Chainable
    */
    public function set_num_per_page($num)
    {
        if (!is_integer($num)) {
            throw new InvalidArgumentException('Num must be an integer.');
        }
        $this->pagination()->set_num_per_page($num);
        return $this;
    }

    /**
    * @return integer
    */
    public function num_per_page()
    {
        return $this->pagination()->num_per_page();
    }

    /**
    * Load a collection from source
    * @param string|null $ident
    * @throws Exception if the database connection fails
    * @return Collection
    */
    public function load($ident = null)
    {
        // Unused.
        unset($ident);

        // Attempt loading from cache
        $ret = $this->cache_load();
        if ($ret !== false) {
            return $ret;
        }

        $db = $this->source()->db();
        if (!$db) {
            throw new Exception('Could not instanciate database connection.');
        }

        /** @todo Filters, pagination, select, etc */
        $q = $this->source()->sql_load();
        Charcoal::logger()->debug($q);
        $collection = new Collection();


        $sth = $db->prepare($q);
        /** @todo Filter binds */
        $sth->execute();
        $sth->setFetchMode(PDO::FETCH_ASSOC);
        $class_name = get_class($this->model());
        while ($obj_data = $sth->fetch()) {
            $obj = new $class_name;
            $obj->set_flat_data($obj_data);
            $collection->add($obj);
        }

        $this->cache_store($collection);

        return $collection;
    }
}
