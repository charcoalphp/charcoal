<?php

namespace Charcoal\Property;

// Intra-module (`charcoal-core`) dependencies
use \Charcoal\Core\AbstractFactory as AbstractFactory;

/**
*
*/
class PropertyFactory extends AbstractFactory
{
    /**
    * @param array $data
    */
    protected function __construct(array $data = null)
    {
        $this->set_factory_mode(AbstractFactory::MODE_IDENT);
        $this->set_base_class('\Charcoal\Property\PropertyInterface');
        $this->set_default_class('\Charcoal\Model\Property');
        if ($data !== null) {
            $this->set_data($data);
        }
    }

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
