<?php

namespace Charcoal\Ui\MenuItem;

use Charcoal\Factory\ResolverFactory;
use Charcoal\Ui\MenuItem\MenuItemInterface;
use Charcoal\Ui\MenuItem\GenericMenuItem;

/**
 * Menu Item Factory
 */
class MenuItemFactory extends ResolverFactory
{
    /**
     * @return string
     */
    public function baseClass()
    {
        return MenuItemInterface::class;
    }

    /**
     * @return string
     */
    public function defaultClass()
    {
        return GenericMenuItem::class;
    }

    /**
     * @return string
     */
    public function resolverSuffix()
    {
        return 'MenuItem';
    }
}
