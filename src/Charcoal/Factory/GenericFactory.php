<?php

namespace Charcoal\Core;

// Local namespace dependencies
use \Charcoal\Factory\AbstractFactory;

/**
*
*/
class GenericFactory extends AbstractFactory
{
    /**
    * {@inheritdoc}
    */
    public function resolve($type)
    {
        return $type;
    }

    /**
    * {@inheritdoc}
    */
    public function is_resolvable($type)
    {
        return !!class_exists($type);
    }
}
