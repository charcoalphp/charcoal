<?php

namespace Charcoal\Source;

// Intra-module (`charcoal-core`) dependencies
use \Charcoal\Core\AbstractFactory as AbstractFactory;

/**
*
*/
class SourceFactory extends AbstractFactory
{
    /**
    * @return array
    */
    public static function types()
    {
        return array_merge(
            parent::types(),
            [
                'database' => '\Charcoal\Source\DatabaseSource'
            ]
        );
    }
}
