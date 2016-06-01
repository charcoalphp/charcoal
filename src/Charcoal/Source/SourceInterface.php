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
     * @param ModelInterface $model The source's model.
     * @return AbstractSource Chainable
     */
    public function setModel(ModelInterface $model);

    /**
     * @return ModelInterface
     */
    public function model();

    /**
     * @param mixed             $ident The ID of the item to load.
     * @param StorableInterface $item  Optional item to load into.
     * @return StorableInterface
     */
    public function loadItem($ident, StorableInterface $item = null);

    /**
     * @param StorableInterface|null $item The item type to load.
     * @return array
     */
    public function loadItems(StorableInterface $item = null);

    /**
     * Save an item (create a new row) in storage.
     *
     * @param StorableInterface $item The object to save.
     * @return mixed The created item ID, or false in case of an error.
     */
    public function saveItem(StorableInterface $item);

    /**
     * Update an item in storage.
     *
     * @param StorableInterface $item       The object to update.
     * @param array             $properties The list of properties to update, if not all.
     * @return boolean Success / Failure
     */
    public function updateItem(StorableInterface $item, array $properties = null);

    /**
     * Delete an item from storage
     *
     * @param StorableInterface $item Optional item to delete. If none, the current model object will be used.
     * @return boolean Success / Failure
     */
    public function deleteItem(StorableInterface $item = null);

    /**
     * @param array $properties The properties.
     * @return ColelectionLoader Chainable
     */
    public function setProperties(array $properties);

    /**
     * @return array
     */
    public function properties();

    /**
     * @param string $property Property ident.
     * @return CollectionLoader Chainable
     */
    public function addProperty($property);

    /**
     * @param array $filters The filters.
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
     * @param string|array|Filter $param   The filter parameter. May the "filter property" or an array / object.
     * @param mixed               $val     Optional. Val, only used if the first argument is a string.
     * @param array               $options Optional. Options, only used if the first argument is a string.
     * @return CollectionLoader (Chainable)
     */
    public function addFilter($param, $val = null, array $options = null);

    /**
     * @param array $orders The orders.
     * @return CollectionLoader Chainable
     */
    public function setOrders(array $orders);

    /**
     * @return array
     */
    public function orders();

    /**
     * @param string|array|Order $param   The order parameter. May the "order property" or an array / object.
     * @param string             $mode    Optional. Mode, only used if the first argument is a string.
     * @param array              $options Optional. Options, only user if the first argument is a string.
     * @return CollectionLoader Chainable
     */
    public function addOrder($param, $mode = 'asc', array $options = null);

    /**
     * @param mixed $param The pagination information.
     * @return CollectionLoader Chainable
     */
    public function setPagination($param);

    /**
     * @return Pagination
     */
    public function pagination();

    /**
     * @param integer $page The page number. Starts with 0.
     * @return CollectionLoader Chainable
     */
    public function setPage($page);

    /**
     * @return integer
     */
    public function page();

    /**
     * @param integer $num The number of item to retrieve per page.
     * @return CollectionLoader Chainable
     */
    public function setNumPerPage($num);

    /**
     * @return integer
     */
    public function numPerPage();
}
