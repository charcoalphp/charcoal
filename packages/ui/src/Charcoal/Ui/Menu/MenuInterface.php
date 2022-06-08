<?php

namespace Charcoal\Ui\Menu;

/**
 * Defines a menu.
 */
interface MenuInterface
{
    /**
     * @param array $items The menu items.
     * @return MenuInterface Chainable
     */
    public function setItems(array $items);

    /**
     * @param array|MenuItemInterface $item A menu item structure or object.
     * @return MenuInterface Chainable
     */
    public function addItem($item);

    /**
     * @return MenuItemInterface[]
     */
    public function items();

    /**
     * @return boolean
     */
    public function hasItems();

    /**
     * @return integer
     */
    public function numItems();
}
