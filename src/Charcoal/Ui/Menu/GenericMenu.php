<?php

namespace Charcoal\Ui\Menu;

// From 'charcoal-ui'
use Charcoal\Ui\Menu\AbstractMenu;

/**
 * A Generic Menu
 *
 * Concreete implementation of {@see \Charcoal\Ui\Menu\MenuInterface}.
 */
class GenericMenu extends AbstractMenu
{
    /**
     * Retrieve the menu type.
     *
     * @return string
     */
    public function type()
    {
        return 'charcoal/ui/menu/generic';
    }
}
