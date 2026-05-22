<?php

namespace Charcoal\Ui\Dashboard;

use Pimple\Container;
// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;

/**
 * Dashboard Builder
 */
class DashboardBuilder
{
    /**
     * The default, concrete, dashboard model.
     *
     * @const string
     */
    public const DEFAULT_TYPE = 'charcoal/ui/dashboard/generic';

    /**
     * Return a new dashboard builder.
     *
     * @param FactoryInterface $factory   A dashboard factory.
     * @param Container        $container The DI container.
     */
    public function __construct(protected \Charcoal\Factory\FactoryInterface $factory, protected \Pimple\Container $container)
    {
    }

    /**
     * Build and return a new dashboard.
     *
     * @param  array|\ArrayAccess $options The dashboard build options.
     * @return DashboardInterface
     */
    public function build($options)
    {
        $objType = ($options['type'] ?? self::DEFAULT_TYPE);

        $obj = $this->factory->create($objType);
        $obj->setData($options);

        return $obj;
    }
}
