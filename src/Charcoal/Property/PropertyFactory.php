<?php

namespace Charcoal\Property;

use \Charcoal\Core\AbstractFactory as AbstractFactory;

class PropertyFactory extends AbstractFactory
{
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
