<?php

declare(strict_types=1);

namespace Charcoal\Ui\Layout;

use Charcoal\Factory\ResolverFactory;
use Charcoal\Ui\Layout\LayoutInterface;
use Charcoal\Ui\Layout\GenericLayout;

/**
 * Layout Factory
 */
class LayoutFactory extends ResolverFactory
{
    #[\Override]
    public function baseClass(): string
    {
        return LayoutInterface::class;
    }

    #[\Override]
    public function defaultClass(): string
    {
        return GenericLayout::class;
    }

    #[\Override]
    public function resolverSuffix(): string
    {
        return 'Layout';
    }
}
