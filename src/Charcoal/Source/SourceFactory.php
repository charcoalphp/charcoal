<?php

namespace Charcoal\Source;

// Moule `charcoal-factory` dependencies
use \Charcoal\Factory\MapFactory;

/**
*
*/
class SourceFactory extends MapFactory
{
    /**
    * @return string
    */
    public function base_class()
    {
        return '\Charcoal\Source\SourceInterface';
    }

    /**
    * @return array
    */
    public function map()
    {
        return [
            'database' => '\Charcoal\Source\DatabaseSource'
        ];
    }
}
