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
     * Store the dashboard factory instance.
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
     * Return a new dashboard builder.
     *
     * @param FactoryInterface $factory   A dashboard factory.
     * @param Container        $container The DI container.
     */
    public function __construct(FactoryInterface $factory, Container $container)
    {
        $this->factory   = $factory;
        $this->container = $container;
    }

    /**
     * Build and return a new dashboard.
     *
     * @param  array|\ArrayAccess $options The dashboard build options.
     * @return DashboardInterface
     */
    public function build($options)
    {
        $objType = isset($options['type']) ? $options['type'] : self::DEFAULT_TYPE;

        $obj = $this->factory->create($objType);
        $obj->setData($options);

        return $obj;
    }
}
