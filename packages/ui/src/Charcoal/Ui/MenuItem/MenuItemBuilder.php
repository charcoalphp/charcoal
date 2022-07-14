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
     * Store the menu item factory instance.
     *
     * @var FactoryInterface
     */
    protected $factory;

    /**
     * Store the dependency-injection container to fulfill the required services.
     *
     * @var Container $container
     */
    protected $container;

    /**
     * Return a new menu item builder.
     *
     * @param FactoryInterface $factory   A menu item factory.
     * @param Container        $container The DI container.
     */
    public function __construct(FactoryInterface $factory, Container $container)
    {
        $this->factory   = $factory;
        $this->container = $container;
    }

    /**
     * Build and return a new menu item.
     *
     * @param  array|\ArrayAccess $options The menu item build options.
     * @return MenuItemInterface
     */
    public function build($options)
    {
        $container = $this->container;
        $objType = isset($options['type']) ? $options['type'] : self::DEFAULT_TYPE;

        $obj = $this->factory->create($objType);
        $obj->setData($options);

        return $obj;
    }
}
