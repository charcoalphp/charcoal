<?php

namespace Charcoal\Source;

// Intra-module (`charcoal-core`) dependencies
use \Charcoal\Model\ModelInterface as ModelInterface;

// Local namespace dependencies
use \Charcoal\Source\SourceInterface as SourceInterface;

/**
* @todo Implement SourceInterface.
*/
interface SourceInterface
{
    /**
    * @param ModelInterface $model
    * @return AbstractSource Chainable
    */
    public function set_model(ModelInterface $model);

    /**
    * @throws Exception if model was not previously set
    * @return ModelInterface
    */
    public function model();


    /**
    * @param mixed              $ident
    * @param StorableInterface $item  Optional item to load into
    * @throws Exception
    * @return StorableInterface
    */
    public function load_item($ident, StorableInterface $item = null);

    /**
    * @param StorableInterface|null $item
    * @return array
    */
    public function load_items(StorableInterface $item = null);

    /**
    * Save an item (create a new row) in storage.
    *
    * @param StorableInterface $item The object to save
    * @throws Exception if a database error occurs
    * @return mixed The created item ID, or false in case of an error
    */
    public function save_item(StorableInterface $item);

    /**
    * Update an item in storage.
    *
    * @param StorableInterface $item       The object to update
    * @param array             $properties The list of properties to update, if not all
    * @return boolean Success / Failure
    */
    public function update_item(StorableInterface $item, $properties = null);

    /**
    * Delete an item from storage
    *
    * @param StorableInterface $item Optional item to delete. If none, the current model object will be used.
    * @throws Exception
    * @return boolean Success / Failure
    */
    public function delete_item(StorableInterface $item = null);

    /**
    * @param array $properties
    * @throws InvalidArgumentException
    * @return ColelectionLoader Chainable
    */
    public function set_properties(array $properties);

    /**
    * @return array
    */
    public function properties();

    /**
    * @param string $property Property ident
    * @throws InvalidArgumentException if property is not a string or empty
    * @return CollectionLoader Chainable
    */
    public function add_property($property);

    /**
    * @param array $filters
    * @throws InvalidArgumentException
    * @return Collection Chainable
    */
    public function set_filters($filters);

    /**
    * @return array
    */
    public function filters();

    /**
    * Add a collection filter to the loader.
    *
    * @param string|array|Filter $param
    * @param mixed               $val     Optional: Only used if the first argument is a string
    * @param array               $options Optional: Only used if the first argument is a string
    * @throws InvalidArgumentException if property is not a string or empty
    * @return CollectionLoader (Chainable)
    */
    public function add_filter($param, $val = null, array $options = null);
    /**
    * @param array $orders
    * @return CollectionLoader Chainable
    */
    public function set_orders($orders);

    /**
    * @return array
    */
    public function orders();

    /**
    * @param string|array|Order $param
    * @param string             $mode          Optional
    * @param array              $order_options Optional
    * @throws InvalidArgumentException
    * @return CollectionLoader Chainable
    */
    public function add_order($param, $mode = 'asc', $order_options = null);

    /**
    * @param mixed $param
    * @return CollectionLoader Chainable
    */
    public function set_pagination($param);

    /**
    * @return Pagination
    */
    public function pagination();

    /**
    * @param integer $page
    * @throws InvalidArgumentException
    * @return CollectionLoader Chainable
    */
    public function set_page($page);

    /**
    * @return integer
    */
    public function page();

    /**
    * @param integer $num
    * @throws InvalidArgumentException
    * @return CollectionLoader Chainable
    */
    public function set_num_per_page($num);

    /**
    * @return integer
    */
    public function num_per_page();
}
