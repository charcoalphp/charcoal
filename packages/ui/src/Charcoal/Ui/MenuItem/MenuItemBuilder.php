<?php

namespace Charcoal\Ui\MenuItem;

// From Pimple
use Pimple\Container;
// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;

/**
 * Menu Item Builder
 */
class MenuItemBuilder
{
    /**
     * The default, concrete, menu item model.
     *
     * @const string
     */
    public const DEFAULT_TYPE = 'charcoal/ui/menu-item/generic';

    /**
     * Return a new menu item builder.
     *
     * @param FactoryInterface $factory   A menu item factory.
     * @param Container        $container The DI container.
     */
    public function __construct(protected \Charcoal\Factory\FactoryInterface $factory, protected \Pimple\Container $container)
    {
    }

    /**
     * Build and return a new menu item.
     *
     * @param  array|\ArrayAccess $options The menu item build options.
     * @return MenuItemInterface
     */
    public function build($options)
    {
        $objType = $options['type'] ?? self::DEFAULT_TYPE;

        $obj = $this->factory->create($objType);
        $obj->setData($options);

        return $obj;
    }
}
