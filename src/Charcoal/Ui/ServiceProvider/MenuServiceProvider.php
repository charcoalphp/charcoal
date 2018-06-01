<?php

namespace Charcoal\Ui\ServiceProvider;

// From Pimple
use Pimple\Container;
use Pimple\ServiceProviderInterface;

// From 'charcoal-ui'
use Charcoal\Ui\Menu\MenuBuilder;
use Charcoal\Ui\Menu\MenuFactory;
use Charcoal\Ui\MenuItem\MenuItemBuilder;
use Charcoal\Ui\MenuItem\MenuItemFactory;

/**
 *
 */
class MenuServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $container A Pimple DI container.
     * @return void
     */
    public function register(Container $container)
    {
        $this->registerMenuServices($container);
        $this->registerMenuItemServices($container);
    }

    /**
     * @param Container $container A Pimple DI container.
     * @return void
     */
    public function registerMenuServices(Container $container)
    {
        /**
         * @param Container $container A Pimple DI container.
         * @return MenuFactory
         */
        $container['menu/factory'] = function(Container $container) {
            $menuFactory = new MenuFactory();
            $menuFactory->setArguments([
                'menu_item_builder' => $container['menu/item/builder']
            ]);
            return $menuFactory;
        };

        /**
         * @param Container $container A Pimple DI container.
         * @return MenuBuilder
         */
        $container['menu/builder'] = function(Container $container) {
            $menuFactory = $container['menu/factory'];
            $menuBuilder = new MenuBuilder($menuFactory, $container);
            return $menuBuilder;
        };
    }

    /**
     * @param Container $container A Pimple DI container.
     * @return void
     */
    public function registerMenuItemServices(Container $container)
    {
        /**
         * @param Container $container A Pimple DI container.
         * @return MenuFactory
         */
        $container['menu/item/factory'] = function() {
            $menuItemFactory = new MenuItemFactory();
            return $menuItemFactory;
        };

        /**
         * @param Container $container A Pimple DI container.
         * @return MenuBuilder
         */
        $container['menu/item/builder'] = function(Container $container) {
            $menuItemFactory = $container['menu/item/factory'];
            $menuItemBuilder = new MenuItemBuilder($menuItemFactory, $container);
            return $menuItemBuilder;
        };
    }
}
