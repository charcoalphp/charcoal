<?php

namespace Charcoal\Ui\MenuItem;

use \Charcoal\Ui\MenuItem\AbstractMenuItem;

/**
 * Generic, concrete MenuItem implementation.
 */
class GenericMenuItem extends AbstractMenuItem
{
    /**
     * @return string
     */
    public function type()
    {
        return 'charcoal/ui/menu-item/generic';
    }
}
