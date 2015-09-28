<?php

namespace Charcoal\Source;

// Dependencies from `PHP`
use \Exception as Exception;
use \InvalidArgumentException as InvalidArgumentException;

// Intra-module (`charcoal-core`) dependencies
use \Charcoal\Config\ConfigurableInterface;
use \Charcoal\Config\ConfigurableTrait;
use \Charcoal\Model\ModelInterface;

// Local namespace dependencies
use \Charcoal\Source\SourceConfig;
use \Charcoal\Source\SourceInterface;
use \Charcoal\Source\Filter;
use \Charcoal\Source\FilterInterface;
use \Charcoal\Source\Order;
use \Charcoal\Source\OrderInterface;
use \Charcoal\Source\Pagination;
use \Charcoal\Source\PaginationInterface;

/**
* Full implementation, as abstract class, of the SourceInterface.
*/
abstract class AbstractSource implements
    SourceInterface,
    ConfigurableInterface
{
    use ConfigurableTrait;

    /**
    * @var ModelInterface $model
    */
    private $model = null;

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
    * The `Pagination` object
    * @var Pagination|null $pagination
    */
    private $pagination = null;

    /**
    * Reset everything but the model.
    *
    * @return AbstractSource Chainable
    */
    public function reset()
    {
        $this->properties = [];
        $this->properties_options = [];
        $this->filters = [];
        $this->orders = [];
        $this->pagination = null;
        return $this;
    }

    /**
    * Initialize the source's properties with an array of data.
    *
    * @param array $data
    * @return AbstractSource Chainable
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
    * Set the source's Model.
    *
    * @param ModelInterface $model
    * @return AbstractSource Chainable
    */
    public function set_model(ModelInterface $model)
    {
        $this->model = $model;
        return $this;
    }

    /**
    * Return the source's Model.
    *
    * @throws Exception if not model was previously set
    * @return ModelInterface
    */
    public function model()
    {
        if ($this->model === null) {
            throw new Exception('No model set.');
        }
        return $this->model;
    }

    /**
    * Set the properties of the source to fetch.
    *
    * This method accepts an array of property identifiers (property ident, as string)
    * that will, if supported, be fetched from the source.
    *
    * If no properties are set, it is assumed that all the Model's properties are to be fetched.
    *
    * @param array $properties
    * @throws InvalidArgumentException
    * @return ColelectionLoader Chainable
    */
    public function set_properties(array $properties)
    {
        $this->properties = [];
        foreach ($properties as $p) {
            $this->add_property($p);
        }
        return $this;
    }

    /**
    * @return array
    */
    public function properties()
    {
        return $this->properties;
    }

    /**
    * @param string $property Property ident
    * @throws InvalidArgumentException if property is not a string or empty
    * @return CollectionLoader Chainable
    */
    public function add_property($property)
    {
        if (!is_string($property)) {
            throw new InvalidArgumentException('Property must be a string.');
        }
        if ($property=='') {
            throw new InvalidArgumentException('Property can not be empty.');
        }
        $this->properties[] = $property;
        return $this;
    }



    /**
    * @param array $filters
    * @throws InvalidArgumentException
    * @return Collection Chainable
    */
    public function set_filters(array $filters)
    {
        $this->filters = [];
        foreach ($filters as $f) {
            $this->add_filter($f);
        }
        return $this;
    }

    /**
    * @return array
    */
    public function filters()
    {
        return $this->filters;
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
        if ($param instanceof FilterInterface) {
            $this->filters[] = $param;
        } elseif (is_array($param)) {
            $filter = $this->create_filter();
            $filter->set_data($param);
            $this->filters[] = $filter;
        } elseif (is_string($param) && $val !== null) {
            $filter = $this->create_filter();
            $filter->set_property($param);
            $filter->set_val($val);
            if (is_array($options)) {
                $filter->set_data($options);
            }
            $this->filters[] = $filter;

        } else {
            throw new InvalidArgumentException('Parameter must be an array or a property ident.');
        }

        return $this;
    }

    /**
    * @return FilterInterface
    */
    protected function create_filter()
    {
        $filter = new Filter();
        return $filter;
    }

    /**
    * @param array $orders
    * @return CollectionLoader Chainable
    */
    public function set_orders(array $orders)
    {
        $this->orders = [];
        foreach ($orders as $o) {
            $this->add_order($o);
        }
        return $this;
    }

    /**
    * @return array
    */
    public function orders()
    {
        return $this->orders;
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
        if ($param instanceof OrderInterface) {
            $this->orders[] = $param;
        } elseif (is_array($param)) {
            $order = $this->create_order();
            $order->set_data($param);
            $this->orders[] = $order;
        } elseif (is_string($param)) {
            $order = $this->create_order();
            $order->set_property($param);
            $order->set_mode($mode);
            if (isset($order_options['values'])) {
                $order->set_values($order_options['values']);
            }
            $this->orders[] = $order;
        } else {
            throw new InvalidArgumentException(
                'Parameter must be an OrderInterface object or a property ident.'
            );
        }

        return $this;
    }

    /**
    * @return OrderInterface
    */
    protected function create_order()
    {
        $order = new Order();
        return $order;
    }

    /**
    * @param mixed $param
    * @throws InvalidArgumentException
    * @return CollectionLoader Chainable
    */
    public function set_pagination($param)
    {
        if ($param instanceof PaginationInterface) {
            $this->pagination = $param;
        } elseif (is_array($param)) {
            $pagination = $this->create_pagination();
            $pagination->set_data($param);
            $this->pagination = $pagination;
        } else {
            throw new InvalidArgumentException('Can not set pagination, invalid argument.');
        }
        return $this;
    }

    /**
    * Get the pagination object.
    *
    * If the pagination wasn't set previously, a new (default / blank) Pagination object will be created.
    * (Always return a `PaginationInterface` object)
    *
    * @return Pagination
    */
    public function pagination()
    {
        if ($this->pagination === null) {
            $this->pagination = $this->create_pagination();
        }
        return $this->pagination;
    }

    /**
    * @return PaginationInterface
    */
    protected function create_pagination()
    {
        $pagination = new Pagination();
        return $pagination;
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
    * ConfigurableTrait > create_config()
    *
    * @param array $data Optional
    * @return SourceConfig
    */
    public function create_config(array $data = null)
    {
        $config = new SourceConfig();
        if (is_array($data)) {
            $config->set_data($data);
        }
        return $config;
    }

    /**
    * @param mixed $ident
    * @param StorableInterface $item  Optional item to load into
    * @throws Exception
    * @return StorableInterface
    */
    abstract public function load_item($ident, StorableInterface $item = null);

        /**
    * @param StorableInterface|null $item
    * @return array
    */
    abstract public function load_items(StorableInterface $item = null);

    /**
    * Save an item (create a new row) in storage.
    *
    * @param StorableInterface $item The object to save
    * @throws Exception if a database error occurs
    * @return mixed The created item ID, or false in case of an error
    */
    abstract public function save_item(StorableInterface $item);

    /**
    * Update an item in storage.
    *
    * @param StorableInterface $item       The object to update
    * @param array             $properties The list of properties to update, if not all
    * @return boolean Success / Failure
    */
    abstract public function update_item(StorableInterface $item, $properties = null);

    /**
    * Delete an item from storage
    *
    * @param StorableInterface $item Optional item to delete. If none, the current model object will be used.
    * @throws Exception
    * @return boolean Success / Failure
    */
    abstract public function delete_item(StorableInterface $item = null);
}
