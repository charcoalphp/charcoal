<?php

namespace Charcoal\Source;

// Intra-module (`charcoal-core`) dependencies
use \Charcoal\Core\ClassMapFactory;

/**
*
*/
class SourceFactory extends ClassMapFactory
{
    /**
    * @param array $data
    */
    public function __construct()
    {
        $this->set_base_class('\Charcoal\Source\SourceInterface');
        $this->set_class_map([
            'database' => '\Charcoal\Source\DatabaseSource'
        ]);
    }
}
