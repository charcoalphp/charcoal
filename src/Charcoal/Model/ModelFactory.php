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
    public function base_class()
    {
        return '\Charcoal\Model\ModelInterface';
    }
}
