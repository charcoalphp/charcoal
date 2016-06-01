<?php

namespace Charcoal\Model;

// Moule `charcoal-factory` dependencies
use \Charcoal\Factory\ResolverFactory;

/**
 *
 */
class ModelFactory extends ResolverFactory
{
    /**
     * @return string
     */
    public function baseClass()
    {
        return '\Charcoal\Model\ModelInterface';
    }
}
