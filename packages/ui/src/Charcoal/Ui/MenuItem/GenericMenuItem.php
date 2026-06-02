<?php

declare(strict_types=1);

namespace Charcoal\Ui\MenuItem;

// From 'charcoal-ui'
use Charcoal\Ui\MenuItem\AbstractMenuItem;

/**
 * A Generic Menu Item
 *
 * Concreete implementation of {@see \Charcoal\Ui\MenuItem\MenuItemInterface}.
 */
class GenericMenuItem extends AbstractMenuItem
{
    /**
     * Retrieve the menu item type.
     */
    #[\Override]
    public function type(): string
    {
        return 'charcoal/ui/menu-item/generic';
    }
}
