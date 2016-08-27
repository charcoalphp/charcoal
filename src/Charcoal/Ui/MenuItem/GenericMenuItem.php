<?php

namespace Charcoal\Ui\MenuItem;

use \Charcoal\Ui\MenuItem\AbstractMenuItem;

/**
 * A Generic Menu Item
 *
 * Concreete implementation of {@see \Charcoal\Ui\MenuItem\MenuItemInterface}.
 */
class GenericMenuItem extends AbstractMenuItem
{
    /**
     * Retrieve the menu item type.
     *
     * @return string
     */
    public function type()
    {
        return 'charcoal/ui/menu-item/generic';
    }
}
