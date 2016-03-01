<?php

namespace Charcoal\Ui\Menu;

use \Charcoal\Ui\UiItemInterface;

/**
 *
 */
interface MenuInterface extends UiItemInterface
{
    /**
     * @param array $items
     * @return MenuInterface Chainable
     */
    public function setItems($items);

    /**
     * @param array|MenuItemInterface $child
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
