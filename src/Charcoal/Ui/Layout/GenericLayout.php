<?php

namespace Charcoal\Ui\Layout;

use \Charcoal\Ui\Layout\AbstractLayout;

/**
 * Concrete layout class.
 */
class GenericLayout extends AbstractLayout
{
    /**
     * Get the UI item's type.
     *
     * @return string
     */
    public function type()
    {
        return 'charcoal/ui/layout/generic';
    }
}
