<?php

namespace Charcoal\Model;

// From 'charcoal-core'
use Charcoal\Validator\AbstractValidator;
use Charcoal\Validator\ValidatableInterface;

/**
 * Model Validator
 */
class ModelValidator extends AbstractValidator
{
    /**
     * @return boolean
     */
    public function validate()
    {
        $result = true;

        $model = $this->model;
        $props = $model->properties();

        foreach ($props as $ident => $prop) {
            if (!($prop instanceof ValidatableInterface) || !$prop['active']) {
                continue;
            }

            if (isset($prop['validatable']) && !$prop['validatable']) {
                continue;
            }

            $value   = $model->propertyValue($ident);
            $isValid = $prop->setVal($value)->validate();
            $prop->clearVal();

            if ($isValid === false) {
                $validator = $prop->validator();
                $this->merge($validator, $ident);
                $result = false;
            }
        }

        return $result;
    }
}
