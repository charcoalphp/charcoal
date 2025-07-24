<?php

namespace Charcoal\Ui\ServiceProvider;

use DI\Container;
// From 'charcoal-ui'
use Charcoal\Ui\Menu\MenuBuilder;
use Charcoal\Ui\Menu\MenuFactory;
use Charcoal\Ui\MenuItem\MenuItemBuilder;
use Charcoal\Ui\MenuItem\MenuItemFactory;
use Psr\Container\ContainerInterface;

/**
 *
 */
class MenuServiceProvider
{
    /**
     * @param Container $container A DI Container.
     * @return void
     */
    public function register(ContainerInterface $container)
    {
        $this->registerMenuServices($container);
        $this->registerMenuItemServices($container);
    }

    /**
     * @param Container $container A DI Container.
     * @return void
     */
    public function registerMenuServices(ContainerInterface $container)
    {
        /**
         * @param Container $container A DI Container.
         * @return MenuFactory
         */
        $container->set('menu/factory', function (Container $container) {
            $menuFactory = new MenuFactory();
            $menuFactory->setArguments([
                'container'         => $container,
                'logger'            => $container->get('logger'),
                'view'              => $container->get('view'),
                'menu_item_builder' => $container->get('menu/item/builder'),
            ]);
            return $menuFactory;
        });

        /**
         * @param Container $container A DI Container.
         * @return MenuBuilder
         */
        $container->set('menu/builder', function (Container $container) {
            $menuFactory = $container->get('menu/factory');
            $menuBuilder = new MenuBuilder($menuFactory, $container);
            return $menuBuilder;
        });
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
     * @param Container $container A DI Container.
     * @return void
     */
    public function registerMenuItemServices(ContainerInterface $container)
    {
        /**
         * @var callable
         */
        $delegate = function (Container $container) {
            $args = [
                'container' => $container,
                'logger'    => $container->get('logger'),
                'view'      => $container->get('view'),
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
         * @param  Container $container A DI Container.
         * @return MenuFactory
         */
        $container->set('menu/item/factory', function (Container $container) use ($delegate) {
            $services = $delegate($container);

            $container->set('menu/item/builder', $services['builder']);
            return $services['factory'];
        });

        /**
         * @param  Container $container A DI Container.
         * @return MenuBuilder
         */
        $container->set('menu/item/builder', function (Container $container) use ($delegate) {
            $services = $delegate($container);

            $container->set('menu/item/factory', $services['factory']);
            return $services['builder'];
        });
    }
}
