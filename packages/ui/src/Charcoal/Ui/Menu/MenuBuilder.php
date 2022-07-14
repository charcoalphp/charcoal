<?php

namespace Charcoal\Ui\Menu;

// From Pimple
use Pimple\Container;
// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;

/**
 * Menu Builder
 */
class MenuBuilder
{
    /**
     * The default, concrete, menu model.
     *
     * @const string
     */
    public const DEFAULT_TYPE = 'charcoal/ui/menu/generic';

    /**
     * Store the menu factory instance.
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
     * Return a new menu builder.
     *
     * @param FactoryInterface $factory   A menu factory.
     * @param Container        $container The DI container.
     */
    public function __construct(FactoryInterface $factory, Container $container)
    {
        $this->factory   = $factory;
        $this->container = $container;
    }

    /**
     * Build and return a new menu.
     *
     * @param  array|\ArrayAccess $options The menu build options.
     * @return MenuInterface
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
