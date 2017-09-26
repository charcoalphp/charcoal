<?php

namespace Charcoal\Ui\Layout;

use Charcoal\Ui\Layout\AbstractLayout;

/**
 * A Generic Layout
 *
 * Concreete implementation of {@see \Charcoal\Ui\Layout\LayoutInterface}.
 */
class GenericLayout extends AbstractLayout
{
    /**
     * Retrieve the layout type.
     *
     * @return string
     */
    public function type()
    {
        return 'charcoal/ui/layout/generic';
    }
}
