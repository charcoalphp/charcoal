<?php

namespace Charcoal\Property;

// Intra-module (`charcoal-core`) dependencies
use \Charcoal\Core\IdentFactory as IdentFactory;

/**
*
*/
class PropertyFactory extends IdentFactory
{
    /**
    * @param array $data
    */
    public function __construct()
    {
        $this->set_base_class('\Charcoal\Property\PropertyInterface');
        $this->set_default_class('\Charcoal\Model\Property');
    }

    /**
    * @param string $classname
    * @return string
    */
    public function prepare_classname($classname)
    {
        return '\Charcoal\Property'.$classname.'Property';
    }
}
