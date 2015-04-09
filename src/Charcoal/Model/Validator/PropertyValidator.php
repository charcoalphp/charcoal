<?php

namespace Charcoal\Model\Validator;

use Charcoal\Model\ModelValidator as ModelValidator;
use Charcoal\Model\Property as Property;

class PropertyValidator extends ModelValidator
{
    public function validate_model(Property $model)
    {
        $model->validate($this);

        return $this;
    }
}
