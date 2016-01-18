<?php

namespace Charcoal\Source;

// Intra-module (`charcoal-core`) dependencies
use \Charcoal\Model\ModelInterface;
use \Charcoal\Source\StorableInterface;

/**
* @todo Implement SourceInterface.
*/
interface SourceInterface
{
    /**
    * @param ModelInterface $model
    * @return AbstractSource Chainable
    */
    public function setModel(ModelInterface $model);

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
    public function loadItem($ident, StorableInterface $item = null);

    /**
    * @param StorableInterface|null $item
    * @return array
    */
    public function loadItems(StorableInterface $item = null);

    /**
    * Save an item (create a new row) in storage.
    *
    * @param StorableInterface $item The object to save
    * @throws Exception if a database error occurs
    * @return mixed The created item ID, or false in case of an error
    */
    public function saveItem(StorableInterface $item);

    /**
    * Update an item in storage.
    *
    * @param StorableInterface $item       The object to update
    * @param array             $properties The list of properties to update, if not all
    * @return boolean Success / Failure
    */
    public function updateItem(StorableInterface $item, $properties = null);

    /**
    * Delete an item from storage
    *
    * @param StorableInterface $item Optional item to delete. If none, the current model object will be used.
    * @throws Exception
    * @return boolean Success / Failure
    */
    public function deleteItem(StorableInterface $item = null);

    /**
    * @param array $properties
    * @throws InvalidArgumentException
    * @return ColelectionLoader Chainable
    */
    public function setProperties(array $properties);

    /**
    * @return array
    */
    public function properties();

    /**
    * @param string $property Property ident
    * @throws InvalidArgumentException if property is not a string or empty
    * @return CollectionLoader Chainable
    */
    public function addProperty($property);

    /**
    * @param array $filters
    * @throws InvalidArgumentException
    * @return Collection Chainable
    */
    public function setFilters(array $filters);

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
    public function addFilter($param, $val = null, array $options = null);
    /**
    * @param array $orders
    * @return CollectionLoader Chainable
    */
    public function setOrders(array $orders);

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
    public function addOrder($param, $mode = 'asc', $order_options = null);

    /**
    * @param mixed $param
    * @return CollectionLoader Chainable
    */
    public function setPagination($param);

    /**
    * @return Pagination
    */
    public function pagination();

    /**
    * @param integer $page
    * @throws InvalidArgumentException
    * @return CollectionLoader Chainable
    */
    public function setPage($page);

    /**
    * @return integer
    */
    public function page();

    /**
    * @param integer $num
    * @throws InvalidArgumentException
    * @return CollectionLoader Chainable
    */
    public function setNumPerPage($num);

    /**
    * @return integer
    */
    public function numPerPage();
}
