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
        $container['menu/factory'] = function (Container $container) {
            $menuFactory = new MenuFactory();
            $menuFactory->setArguments([
                'container'         => $container,
                'logger'            => $container['logger'],
                'view'              => $container['view'],
                'menu_item_builder' => $container['menu/item/builder'],
            ]);
            return $menuFactory;
        };

        /**
         * @param Container $container A Pimple DI container.
         * @return MenuBuilder
         */
        $container['menu/builder'] = function (Container $container) {
            $menuFactory = $container['menu/factory'];
            $menuBuilder = new MenuBuilder($menuFactory, $container);
            return $menuBuilder;
        };
    }

    /**
     * Registers the menu item services.
     *
     * The `MenuItemBuilder` is required by the `AbstractMenuItem` and the `MenuItemFactory`
     * but, awkwardly, the `MenuItemBuilder` itself requires the `MenuItemFactory`.
     *
     * To avert the infinity loop, the `MenuItemFactory` and `MenuItemBuilder` must be
     * instantiated at the same time.
     *
     * @param Container $container A Pimple DI container.
     * @return void
     */
    public function registerMenuItemServices(Container $container)
    {
        /**
         * @var callable
         */
        $delegate = function (Container $container) {
            $args = [
                'container' => $container,
                'logger'    => $container['logger'],
                'view'      => $container['view'],
            ];

            $factory = new MenuItemFactory();
            $builder = new MenuItemBuilder($factory, $container);

            $args['menu_item_builder'] = $builder;
            $factory->setArguments($args);

            return [
                'factory' => $factory,
                'builder' => $builder,
            ];
        };

        /**
         * @param  Container $container A Pimple DI container.
         * @return MenuFactory
         */
        $container['menu/item/factory'] = function (Container $container) use ($delegate) {
            $services = $delegate($container);

            $container['menu/item/builder'] = $services['builder'];
            return $services['factory'];
        };

        /**
         * @param  Container $container A Pimple DI container.
         * @return MenuBuilder
         */
        $container['menu/item/builder'] = function (Container $container) use ($delegate) {
            $services = $delegate($container);

            $container['menu/item/factory'] = $services['factory'];
            return $services['builder'];
        };
    }
}
