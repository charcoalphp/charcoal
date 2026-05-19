<?php

declare(strict_types=1);

namespace Charcoal\Property;

// From 'charcoal-factory'
use Charcoal\Factory\ResolverFactory;

/**
 *
 */
class PropertyFactory extends ResolverFactory
{
    #[\Override]
    public function baseClass(): string
    {
        return \Charcoal\Property\PropertyInterface::class;
    }

    #[\Override]
    public function defaultClass(): string
    {
        return \Charcoal\Property\GenericProperty::class;
    }

    #[\Override]
    public function resolverPrefix(): string
    {
        return '\Charcoal\Property';
    }

    #[\Override]
    public function resolverSuffix(): string
    {
        return 'Property';
    }
}
