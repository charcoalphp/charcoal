<?php

namespace Charcoal\Model;

use Charcoal\Validator\AbstractValidator as AbstractValidator;

/**
*
*/
class ModelValidator extends AbstractValidator
{
    /**
    * @return boolean
    */
    public function validate()
    {
        $model = $this->_model;

        //$model->validate($this);

        $props = $model->properties();

        foreach ($props as $ident => $p) {
            if (!$p->active()) {
                continue;
            }

            //$property_validator = $p->validator()->validate_model($p);
            //$this->merge($property_validator, $ident);
        }

        return $this;
    }
}
