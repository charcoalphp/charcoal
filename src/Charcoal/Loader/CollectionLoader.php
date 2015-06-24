<?php

namespace Charcoal\Loader;

use \Exception as Exception;
use \InvalidArgumentException as InvalidArgumentException;

use \Charcoal\Model\ModelInterface as ModelInterface;
use \Charcoal\Model\Collection as Collection;
use \Charcoal\Loader\CollectionLoader\Filter as Filter;
use \Charcoal\Loader\CollectionLoader\Order as Order;
use \Charcoal\Loader\CollectionLoader\Pagination as Pagination;


/**
* Collection Loader
*/
class CollectionLoader extends AbstractLoader
{
    private $_properties = [];
    private $_properties_options = [];
    private $_filters = [];
    private $_orders = [];
    private $_pagination = null;

    private $_source = null;
    private $_model = null;

    /**
    * @param array $data
    * @throws InvalidArgumentException
    * @return CollectionLoader Chainable
    */
    public function set_data($data)
    {
        if (!is_array($data)) {
            throw new InvalidArgumentException('Data must be an array');
        }
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
    * @return mixed
    */
    public function source()
    {
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
        if (!is_array($properties)) {
            throw new InvalidArgumentException('Properties must be an array');
        }
        $this->_properties = [];
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
        return $this->_properties;
    }

    /**
    * @param string $property Property ident
    * @throws InvalidArgumentException if property is not a string or empty
    * @return CollectionLoader Chainable
    */
    public function add_property($property)
    {
        if (!is_string($property)) {
            throw new InvalidArgumentException('Property must be a string');
        }
        if ($property=='') {
            throw new InvalidArgumentException('Property can not be empty');
        }
        $this->_properties[] = $property;
        return $this;
    }


    /**
    * @param array $filters
    * @throws InvalidArgumentException
    * @return Collection Chainable
    */
    public function set_filters($filters)
    {
        if (!is_array($filters)) {
            throw new InvalidArgumentException('Filters must be an array');
        }
        $this->_filters = [];
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
        if (!isset($this->_filters)) {
            $this->_filters = [];
        }
        return $this->_filters;
    }

    /**
    * Add a collection filter to the loader.
    *
    * There are 3 different ways of adding a filter:
    * - as a `Filter` object, in which case it will be added directly.
    *   - `add_filter($obj);`
    * - as an array of options, which will be used to build the `Filter` object
    *   - `add_filter(['property'=>'foo', 'val'=>42, 'operator'=>'<=']);`
    * - as 3 parameters: `property`, `val` and `options`
    *   - `add_filter('foo', 42, ['operator'=>'<=']);`
    *
    * @param string|array|Filter $param
    * @param mixed               $val     Optional: Only used if the first argument is a string
    * @param array               $options Optional: Only used if the first argument is a string
    * @throws InvalidArgumentException if property is not a string or empty
    * @return CollectionLoader (Chainable)
    */
    public function add_filter($param, $val = null, $options = null)
    {
        if ($param instanceof Filter) {
            $this->_filters[] = $param;
        } else if (is_array($param)) {
            $filter = new Filter();
            $filter->set_data($param);
            $this->_filters[] = $filter;
        } else if (is_string($param) && $val !== null) {
            $filter = new Filter();
            $filter->set_property($param);
            $filter->set_val($val);
            $filter->set_data($options);
            $this->_filters[] = $filter;

        } else {
            throw new InvalidArgumentException('Parameter must be an array or a property ident');
        }

        return $this;
    }

    /**
    * @param array $orders
    * @return CollectionLoader Chainable
    */
    public function set_orders($orders)
    {
        $this->_orders = [];
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
        if (!isset($this->_orders)) {
            $this->_orders = [];
        }
        return $this->_orders;
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
        if ($param instanceof Order) {
            $this->_orders[] = $param;
        } else if (is_array($param)) {
            $order = new Order();
            $order->set_data($param);
            $this->_orders[] = $order;
        } else if (is_string($param)) {
            $order = new Order();
            $order->set_property($param);
            $order->set_mode($mode);
            if (isset($order_options['values'])) {
                $order->set_values($order_options['values']);
            }
            $this->_orders[] = $order;
        } else {
            throw new InvalidArgumentException('Parameter must be an Order object or a property ident');
        }

        return $this;
    }

    /**
    * @param mixed $param
    * @return CollectionLoader Chainable
    */
    public function set_pagination($param)
    {
        if ($param instanceof Pagination) {
            $this->_pagination = $param;
        } else if (is_array($param)) {
            $pagination = new Pagination();
            $pagination->set_data($param);
            $this->_pagination = $pagination;
        }
        return $this;
    }

    /**
    * @return Pagination
    */
    public function pagination()
    {
        if (!isset($this->_pagination)) {
            $this->_pagination = new Pagination();
        }
        return $this->_pagination;
    }

    /**
    * @param integer $page
    * @throws InvalidArgumentException
    * @return CollectionLoader Chainable
    */
    public function set_page($page)
    {
        if (!is_integer($page)) {
            throw new InvalidArgumentException('Page must be an integer');
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
            throw new InvalidArgumentException('Num must be an integer');
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
    * Load a collection
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
            throw new Exception('Could not instanciate database connection');
        }

        // @todo Filters, pagination, select, etc
        $q = $this->sql();

        $collection = new Collection();

        $db = $this->source()->db();

        $sth = $db->prepare($q);
        // @todo filter binds
        $sth->execute();
        $sth->setFetchMode(\PDO::FETCH_ASSOC);
        while ($obj_data = $sth->fetch()) {
            // @todo Custom class
            $class_name = get_class($this->model());
            // $obj = ModelFactory::instance()->get(
            $obj = new $class_name;
            // $obj = new \Charcoal\Model\Object();
            $obj->set_flat_data($obj_data);
            $collection->add($obj);
        }

        $this->cache_store($collection);

        return $collection;
    }

    /**
    * @throws Exception if the source does not have a table defined
    * @return string
    */
    public function sql()
    {
        $table = $this->source()->table();
        if (!$table) {
            throw new Exception('No table defined');
        }

        $selects = $this->sql_select();
        $tables = '`'.$table.'` as obj_table';
        $filters = $this->sql_filters();
        $orders = $this->sql_orders();
        $limits = $this->sql_pagination();

        $q = 'select '.$selects.' from '.$tables.$filters.$orders.$limits;
        //var_dump($q);
        return $q;
    }

    /**
    * @return string
    */
    protected function sql_select()
    {
        $properties = $this->properties();
        if (empty($properties)) {
            return 'obj_table.*';
        }

        $sql = '';
        $props_sql = [];
        foreach ($properties as $p) {
            $props_sql[] = 'obj_table.`'.$p.'`';
        }
        if (!empty($props_sql)) {
            $sql = implode(', ', $props_sql);
        }

        return $sql;
    }

    /**
    * @return string
    * @todo 2015-03-04 Use bindings for filters value
    */
    protected function sql_filters()
    {
        $sql = '';

        // Process filters
        if (!empty($this->_filters)) {
            $filters_sql = [];
            foreach ($this->_filters as $f) {
                $f_sql = $f->sql();
                if ($f_sql) {
                    $filters_sql[] = [
                    'sql'        => $f->sql(),
                    'operand'    => $f->operand()
                    ];
                }
            }
            if (!empty($filters_sql)) {
                $sql .= ' WHERE';
                $i = 0;

                foreach ($filters_sql as $f) {
                    if ($i > 0) {
                        $sql .= ' '.$f['operand'];
                    }
                    $sql .= ' '.$f['sql'];
                    $i++;
                }
            }

        }

        return $sql;
    }

    /**
    * @return string
    */
    protected function sql_orders()
    {
        $sql = '';

        if (!empty($this->_orders)) {
            $orders_sql = [];
            foreach ($this->_orders as $o) {
                $orders_sql[] = $o->sql();
            }
            if (!empty($orders_sql)) {
                $sql = ' ORDER BY '.implode(', ', $orders_sql);
            }
        }

        return $sql;
    }

    /**
    * @return string
    */
    protected function sql_pagination()
    {
        return $this->pagination()->sql();
    }
}
