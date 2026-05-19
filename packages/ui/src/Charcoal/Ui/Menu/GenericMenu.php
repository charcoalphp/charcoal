<?php

declare(strict_types=1);

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
     */
    #[\Override]
    public function type(): string
    {
        return 'charcoal/ui/menu/generic';
    }
}
