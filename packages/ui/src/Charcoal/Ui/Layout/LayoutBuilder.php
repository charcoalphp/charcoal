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
     * Return a new layout builder.
     *
     * @param FactoryInterface $factory   A layout factory.
     * @param Container        $container The DI container.
     */
    public function __construct(protected \Charcoal\Factory\FactoryInterface $factory, protected \Pimple\Container $container)
    {
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
        $objType = $options['type'] ?? self::DEFAULT_TYPE;

        $obj = $this->factory->create($objType, [
            'logger' => $container['logger'],
            'view'   => $container['view']
        ]);
        $obj->setData($options);

        return $obj;
    }
}
