<?php

namespace Charcoal\Ui\Menu;

use \Charcoal\Ui\Menu\AbstractMenu;

/**
 * Generic, concrete Menu implementation.
 */
class GenericMenu extends AbstractMenu
{
    /**
     * @return string
     */
    public function type()
    {
        return 'charcoal/ui/menu/generic';
    }
}
