<?php

namespace Charcoal\Ui\Menu;

use \Charcoal\Factory\ResolverFactory;

/**
 * Menu Factory
 */
class MenuFactory extends ResolverFactory
{
    /**
     * @return string
     */
    public function baseClass()
    {
        return '\Charcoal\Ui\Menu\MenuInterface';
    }

    /**
     * @return string
     */
    public function defaultClass()
    {
        return '\Charcoal\Ui\Menu\GenericMenu';
    }

    /**
     * @return string
     */
    public function resolverSuffix()
    {
        return 'Menu';
    }
}
