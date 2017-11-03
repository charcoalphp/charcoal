<?php

namespace Charcoal\Tests\Mock;

// From 'charcoal-core'
use Charcoal\Source\AbstractSource;
use Charcoal\Source\StorableInterface;

/**
 *
 */
class SourceMock extends AbstractSource
{
    /**
     * Load item by the primary key.
     *
     * @param  mixed             $ident Ident can be any scalar value.
     * @param  StorableInterface $item  Optional item to load into.
     * @return StorableInterface
     */
    public function loadItem($ident, StorableInterface $item = null)
    {
        return null;
    }

    /**
     * Load items for the given model.
     *
     * @param  StorableInterface|null $item Optional model.
     * @return StorableInterface[]
     */
    public function loadItems(StorableInterface $item = null)
    {
        return [];
    }

    /**
     * Save an item (create a new row) in storage.
     *
     * @param  StorableInterface $item The object to save.
     * @return mixed The created item ID, otherwise FALSE.
     */
    public function saveItem(StorableInterface $item)
    {
        return 1;
    }

    /**
     * Update an item in storage.
     *
     * @param  StorableInterface $item       The object to update.
     * @param  array             $properties The list of properties to update, if not all.
     * @return boolean TRUE if the item was updated, otherwise FALSE.
     */
    public function updateItem(StorableInterface $item, array $properties = null)
    {
        return true;
    }

    /**
     * Delete an item from storage.
     *
     * @param  StorableInterface $item Optional item to delete. If none, the current model object will be used.
     * @return boolean TRUE if the item was deleted, otherwise FALSE.
     */
    public function deleteItem(StorableInterface $item = null)
    {
        return true;
    }
}
