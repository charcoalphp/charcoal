<?php

declare(strict_types=1);

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
     */
    public function type(): string
    {
        return 'charcoal/ui/layout/generic';
    }
}
