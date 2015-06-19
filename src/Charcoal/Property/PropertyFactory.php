<?php

namespace Charcoal\Property;

// From `charcoal-core`
use \Charcoal\Core\AbstractFactory as AbstractFactory;

/**
*
*/
class PropertyFactory extends AbstractFactory
{
    /**
    * @param string $type
    * @return PropertyInterface
    */
    public function get($type)
    {
        $class_name = '\Charcoal\Property\\'.str_replace('_', '\\', ucfirst($type)).'Property';
        if (class_exists($class_name)) {
            return new $class_name();
        } else {
            return new \Charcoal\Model\Property();
        }
    }
}
