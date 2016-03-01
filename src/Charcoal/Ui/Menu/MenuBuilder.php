<?php

namespace Charcoal\Ui\Menu;

use \Pimple\Container;

use \Charcoal\Factory\FactoryInterface;

/**
 *
 */
class MenuBuilder
{
    const DEFAULT_TYPE = 'charcoal/ui/menu/generic';

    /**
     * @var FactoryInterface $factory
     */
    protected $factory;

    /**
     * A Pimple dependency-injection container
     * @var Container $container
     */
    protected $container;

    /**
     * @param FactoryInterface $factory An object factory.
     * @param Container $container The DI container.
     */
    public function __construct(FactoryInterface $factory, Container $container)
    {
        $this->factory = $factory;
        $this->container = $container;
    }

    /**
     * @param array|\ArrayAccess $options The form group build options / config.
     * @return MenuInterface
     */
    public function build($options)
    {
        $container = $this->container;
        $objType = isset($options['type']) ? $options['type'] : self::DEFAULT_TYPE;

        $obj = $this->factory->create($objType, [
            'logger'    =>  $container['logger'],
            'view'      =>  $container['view'],
            'menu_item_builder' => $container['menu/item/builder']
        ]);
        $obj->setData($options);
        return $obj;
    }
}
