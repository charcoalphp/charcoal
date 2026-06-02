<?php

declare(strict_types=1);

namespace Charcoal\Ui\MenuItem;

use Charcoal\Factory\ResolverFactory;
use Charcoal\Ui\MenuItem\MenuItemInterface;
use Charcoal\Ui\MenuItem\GenericMenuItem;

/**
 * Menu Item Factory
 */
class MenuItemFactory extends ResolverFactory
{
    #[\Override]
    public function baseClass(): string
    {
        return MenuItemInterface::class;
    }

    #[\Override]
    public function defaultClass(): string
    {
        return GenericMenuItem::class;
    }

    #[\Override]
    public function resolverSuffix(): string
    {
        return 'MenuItem';
    }
}
