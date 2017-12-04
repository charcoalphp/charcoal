<?php

namespace Charcoal\Source;

// From 'charcoal-core'
use Charcoal\Source\FilterCollectionInterface;
use Charcoal\Source\OrderCollectionInterface;
use Charcoal\Source\PaginationInterface;
use Charcoal\Source\ModelAwareInterface;
use Charcoal\Source\StorableInterface;

/**
 * Describes a data storage source handler.
 */
interface SourceInterface extends
    FilterCollectionInterface,
    OrderCollectionInterface,
    ModelAwareInterface
{
    /**
     * Set the properties of the source to fetch.
     *
     * This method accepts an array of property identifiers
     * that will, if supported, be fetched from the source.
     *
     * If no properties are set, it is assumed that
     * all model propertiesare to be fetched.
     *
     * @param  (string|\Charcoal\Property\PropertyInterface)[] $properties One or more property keys to set.
     * @return self
     */
    public function setProperties(array $properties);

    /**
     * Determine if the source has any properties to fetch.
     *
     * @return boolean TRUE if properties are defined, otherwise FALSE.
     */
    public function hasProperties();

    /**
     * Get the properties of the source to fetch.
     *
     * @return string[]
     */
    public function properties();

    /**
     * Add a property of the source to fetch.
     *
     * @param  string|\Charcoal\Property\PropertyInterface $property A property key.
     * @throws \InvalidArgumentException If property is not a string, empty, or invalid.
     * @return self
     */
    public function addProperty($property);

    /**
     * Remove a property of the source to fetch.
     *
     * @param  string|\Charcoal\Property\PropertyInterface $property A property key.
     * @throws \InvalidArgumentException If property is not a string, empty, or invalid.
     * @return self
     */
    public function removeProperty($property);

    /**
     * Set query pagination.
     *
     * @param  mixed        $param The pagination object or array.
     * @param  integer|null $limit The number of results to for the $param if a page number.
     * @throws \InvalidArgumentException If the $param argument is invalid.
     * @return self
     */
    public function setPagination($param, $limit = null);

    /**
     * Get query pagination.
     *
     * @return PaginationInterface
     */
    public function pagination();

    /**
     * Set the page number.
     *
     * @param  integer $page The current page.
     *     Pages should start at 1.
     * @throws \InvalidArgumentException If the parameter is not numeric or < 0.
     * @return self
     */
    public function setPage($page);

    /**
     * Retrieve the page number.
     *
     * @return integer
     */
    public function page();

    /**
     * Set the number of results per page.
     *
     * @param  integer $count The number of results to return, per page.
     *     Use 0 to request all results.
     * @throws \InvalidArgumentException If the parameter is not numeric or < 0.
     * @return self
     */
    public function setNumPerPage($count);

    /**
     * Retrieve the number of results per page.
     *
     * @return integer
     */
    public function numPerPage();

    /**
     * Load item by the primary key.
     *
     * @param  mixed             $ident Ident can be any scalar value.
     * @param  StorableInterface $item  Optional item to load into.
     * @return StorableInterface
     */
    public function loadItem($ident, StorableInterface $item = null);

    /**
     * Load items for the given model.
     *
     * @param  StorableInterface|null $item Optional model.
     * @return StorableInterface[]
     */
    public function loadItems(StorableInterface $item = null);

    /**
     * Save an item (create a new row) in storage.
     *
     * @param  StorableInterface $item The object to save.
     * @throws \Exception If a storage error occurs.
     * @return mixed The created item ID, otherwise FALSE.
     */
    public function saveItem(StorableInterface $item);

    /**
     * Update an item in storage.
     *
     * @param  StorableInterface $item       The object to update.
     * @param  array             $properties The list of properties to update, if not all.
     * @return boolean TRUE if the item was updated, otherwise FALSE.
     */
    public function updateItem(StorableInterface $item, array $properties = null);

    /**
     * Delete an item from storage.
     *
     * @param  StorableInterface $item Optional item to delete. If none, the current model object will be used.
     * @throws UnexpectedValueException If the item does not have an ID.
     * @return boolean TRUE if the item was deleted, otherwise FALSE.
     */
    public function deleteItem(StorableInterface $item = null);
}
