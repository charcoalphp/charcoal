<?php

namespace Charcoal\Tests\Mock;

use Charcoal\Source\AbstractSource;
use Charcoal\Source\StorableInterface;

class SourceMock extends AbstractSource
{
    /**
     * @param mixed             $ident The ID of the item to load.
     * @param StorableInterface $item  Optional item to load into.
     * @return ModelInterface
     */
    public function loadItem($ident, StorableInterface $item = null)
    {
        return null;
    }

    /**
     * @param ModelInterface|null $item The model to load items from.
     * @return array
     */
    public function loadItems(StorableInterface $item = null)
    {
        return [];
    }

    /**
     * Save an item (create a new row) in storage.
     *
     * @param StorableInterface $item The object to save.
     * @return mixed The created item ID, or false in case of an error.
     */
    public function saveItem(StorableInterface $item)
    {
        return 1;
    }

    /**
     * Update an item in storage.
     *
     * @param StorableInterface $item       The object to update.
     * @param array             $properties The list of properties to update, if not all.
     * @return boolean Success / Failure
     */
    public function updateItem(StorableInterface $item, array $properties = null)
    {
        return true;
    }

    /**
     * Delete an item from storage
     *
     * @param StorableInterface $item Optional item to delete. If none, the current model object will be used..
     * @return boolean Success / Failure
     */
    public function deleteItem(StorableInterface $item = null)
    {
        return true;
    }
}
