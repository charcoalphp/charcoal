<?php

declare(strict_types=1);

namespace Charcoal\Ui\Menu;

use Charcoal\Factory\ResolverFactory;
use Charcoal\Ui\Menu\MenuInterface;
use Charcoal\Ui\Menu\GenericMenu;

/**
 * Menu Factory
 */
class MenuFactory extends ResolverFactory
{
    #[\Override]
    public function baseClass(): string
    {
        return MenuInterface::class;
    }

    #[\Override]
    public function defaultClass(): string
    {
        return GenericMenu::class;
    }

    #[\Override]
    public function resolverSuffix(): string
    {
        return 'Menu';
    }
}
