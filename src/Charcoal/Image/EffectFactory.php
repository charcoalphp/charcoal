<?php

namespace Charcoal\Image;

use \InvalidArgumentException as InvalidArgumentException;

// File copied from `charcoal-core`
use \Charcoal\Image\AbstractFactory as AbstractFactory;

class EffectFactory extends AbstractFactory
{
    /**
    * @param string $type
    * @throws InvalidArgumentException
    * @return TemplateInterface
    */
    public function create($type)
    {
        if (!is_string($type)) {
            throw new InvalidArgumentException('Type must be a string');
        }
        if (!$this->is_type_available($type)) {
            throw new InvalidArgumentException(sprintf('Type "%s" is not a valid effect type', $type));
        }
        $class_name = $this->ident_to_classname($type);
        return new $class_name();
    }

    /**
    * Returns wether a type is available
    *
    * @param string $type The type to check
    * @return boolean True if the type is available, false if not
    */
    public function is_type_available($type)
    {
        $class_name = $this->ident_to_classname($type);
        return class_exists($class_name);
    }
}
