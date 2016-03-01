<?php

namespace Charcoal\Ui\Form;

use \Charcoal\Factory\ResolverFactory;

/**
 *
 */
class DashboardFactory extends ResolverFactory
{
    /**
     * @return string
     */
    public function baseClass()
    {
        return '\Charcoal\Ui\Dashboard\DashboardInterface';
    }

    /**
     * @return string
     */
    public function defaultClass()
    {
        return '\Charcoal\Ui\Dashboard\GenericDashboard';
    }

    /**
     * @return string
     */
    public function resolverSuffix()
    {
        return 'Dashboard';
    }
}
