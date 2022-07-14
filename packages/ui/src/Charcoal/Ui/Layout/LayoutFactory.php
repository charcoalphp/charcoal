<?php

namespace Charcoal\Ui\Layout;

use Charcoal\Factory\ResolverFactory;
use Charcoal\Ui\Layout\LayoutInterface;
use Charcoal\Ui\Layout\GenericLayout;

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
        return LayoutInterface::class;
    }

    /**
     * @return string
     */
    public function defaultClass()
    {
        return GenericLayout::class;
    }

    /**
     * @return string
     */
    public function resolverSuffix()
    {
        return 'Layout';
    }
}
