<?php

namespace Charcoal\Property;

// From 'charcoal-core'
use Charcoal\Validator\AbstractValidator;
use Charcoal\Validator\ValidatableInterface;

/**
 * Property Validator
 */
class PropertyValidator extends AbstractValidator
{
    /**
     * @return boolean
     */
    public function validate()
    {
        $result = true;

        // The model, in this case, should be a PropertyInterface
        $property = $this->model;

        if (!$property['validatable']) {
            return true;
        }

        $methods = $property->validationMethods();
        foreach ($methods as $method) {
            $method = $this->camelize($method);

            $func = [ $property, 'validate'.ucfirst($method) ];
            if (is_callable($func)) {
                $result = $result && call_user_func($func);
            }
        }

        return $result;
    }
}
