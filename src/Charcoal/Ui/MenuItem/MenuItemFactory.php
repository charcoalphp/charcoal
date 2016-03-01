<?php

namespace Charcoal\Ui\MenuItem;

use \Charcoal\Factory\ResolverFactory;

/**
 *
 */
class MenuItemFactory extends ResolverFactory
{
    /**
     * @return string
     */
    public function baseClass()
    {
        return '\Charcoal\Ui\MenuItem\MenuItemInterface';
    }

    /**
     * @return string
     */
    public function defaultClass()
    {
        return '\Charcoal\Ui\MenuItem\GenericMenuItem';
    }

    /**
     * @return string
     */
    public function resolverSuffix()
    {
        return 'MenuItem';
    }
}
