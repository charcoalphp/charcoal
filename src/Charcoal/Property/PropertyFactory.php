<?php

namespace Charcoal\Property;

// Moule `charcoal-factory` dependencies
use \Charcoal\Factory\ResolverFactory;

/**
*
*/
class PropertyFactory extends ResolverFactory
{
    /**
    * @return string
    */
    public function base_class()
    {
        return '\Charcoal\Property\PropertyInterface';
    }

    /**
    * @return string
    */
    public function default_class()
    {
        return '\Charcoal\Property\GenericProperty';
    }

    /**
    * @return string
    */
    public function resolver_prefix()
    {
        return '\Charcoal\Property';
    }

    /**
    * @return string
    */
    public function resolver_suffix()
    {
        return 'Property';
    }
}
