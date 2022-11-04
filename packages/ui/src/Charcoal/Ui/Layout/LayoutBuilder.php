<?php

namespace Charcoal\Ui\Layout;

// From Pimple
use Pimple\Container;
// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;

/**
 * Layout Builder
 */
class LayoutBuilder
{
    /**
     * The default, concrete, layout model.
     *
     * @const string
     */
    public const DEFAULT_TYPE = 'charcoal/ui/layout/generic';

    /**
     * Store the layout factory instance.
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
     * Return a new layout builder.
     *
     * @param FactoryInterface $factory   A layout factory.
     * @param Container        $container The DI container.
     */
    public function __construct(FactoryInterface $factory, Container $container)
    {
        $this->factory   = $factory;
        $this->container = $container;
    }

    /**
     * Build and return a new layout.
     *
     * @param  array|\ArrayAccess $options The layout build options.
     * @return LayoutInterface
     */
    public function build($options)
    {
        $container = $this->container;
        $objType = isset($options['type']) ? $options['type'] : self::DEFAULT_TYPE;

        $obj = $this->factory->create($objType, [
            'logger' => $container['logger'],
            'view'   => $container['view']
        ]);
        $obj->setData($options);

        return $obj;
    }
}
