<?php

namespace Charcoal\Ui\Menu;

use Charcoal\Factory\ResolverFactory;
use Charcoal\Ui\Menu\MenuInterface;
use Charcoal\Ui\Menu\GenericMenu;

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
        return MenuInterface::class;
    }

    /**
     * @return string
     */
    public function defaultClass()
    {
        return GenericMenu::class;
    }

    /**
     * @return string
     */
    public function resolverSuffix()
    {
        return 'Menu';
    }
}
