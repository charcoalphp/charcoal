<?php

namespace Charcoal\Ui\Layout;

use \Charcoal\Factory\ResolverFactory;

/**
 * Layout Factory
 */
class LayoutFactory extends ResolverFactory
{
    /**
     * @return string
     */
    public function baseClass()
    {
        return '\Charcoal\Ui\Layout\LayoutInterface';
    }

    /**
     * @return string
     */
    public function defaultClass()
    {
        return '\Charcoal\Ui\Layout\GenericLayout';
    }

    /**
     * @return string
     */
    public function resolverSuffix()
    {
        return 'Layout';
    }
}
