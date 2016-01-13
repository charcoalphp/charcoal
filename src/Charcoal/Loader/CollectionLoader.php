<?php

namespace Charcoal\Loader;

// Intra-module (`charcoal-core`) dependencies
use \Exception;
use \InvalidArgumentException;

// PHP Modules dependencies
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
*
* @uses \Charcoal\Model\Collection
*/
class CollectionLoader
{
    /**
    * @var array $properties
    */
    private $properties = [];
    /**
    * @var array $properties_options
    */
    private $properties_options = [];
    /**
    * Array of `Filter` objects
    * @var array $filters
    */
    private $filters = [];
    /**
    * Array of `Order` object
    * @var array $orders
    */
    private $orders = [];
    /**
    * The `Pagniation` object
    * @var Pagination|null $pagination
    */
    private $pagination = null;

    /**
    * The source to load the object from
    * @var SourceInterface $source
    */
    private $source = null;
    /**
    * The model to load the collection from
    * @var ModelInterface $model
    */
    private $model = null;

    /**
    * @param array $data
    * @return CollectionLoader Chainable
    */
    public function set_data(array $data)
    {
        foreach ($data as $prop => $val) {
            $func = [$this, 'set_'.$prop];
            if (is_callable($func)) {
                call_user_func($func, $val);
                unset($data[$prop]);
            } else {
                $this->{$prop} = $val;
            }
        }
        return $this;
    }

    /**
    * @param mixed $source
    * @return CollectionLoader Chainable
    */
    public function set_source($source)
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
        if ($this->source === null) {
            throw new Exception('No source set.');
        }
        return $this->source;
    }

    /**
    * @param ModelInterface $model
    * @return Source Chainable
    */
    public function set_model(ModelInterface $model)
    {
        $this->model = $model;
        $this->set_source($model->source());
        return $this;
    }

    /**
    * @throws Exception if not model was previously set
    * @return Model
    */
    public function model()
    {
        if ($this->model === null) {
            throw new Exception('No model set.');
        }
        return $this->model;
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
    * @param array $keywords
    * @return CollectionLoader Chainable
    */
    public function set_keywords()
    {
        foreach ($keywords as $k) {
            $keyword = $k[0];
            $properties = isset($k[1]) ? $k[1] : null;
            $this->add_keyword($keyword, $properties);
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
    public function add_keyword($keyword, $properties = null)
    {
        $model = $this->model();
        if (!is_array($properties) || empty($properties)) {
            // @todo Load from
            $properties = [];
        }

        foreach ($properties as $property_ident) {
            $prop = $model->p($property_ident);
            $val = ('%'.$keyword.'%');
            $this->add_filter([
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
            $obj = new $class_name([
                'logger'=>Charcoal::logger()
            ]);
            $obj->set_flat_data($obj_data);
            $collection->add($obj);
        }

        return $collection;
    }
}
