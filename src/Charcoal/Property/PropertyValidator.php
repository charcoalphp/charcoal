<?php

namespace Charcoal\Property;

use Charcoal\Validator\AbstractValidator as AbstractValidator;

class PropertyValidator extends AbstractValidator
{
    /**
    * @return boolean
    */
    public function validate()
    {
        $model = $this->_model;

        $ret = true;
        $validation_methods = $model->validation_methods();
        foreach ($validation_methods as $m) {
            $fn = [$model, 'validate_'.$m];
            if (is_callable($fn)) {
                $ret = $ret && call_user_func($fn);
            }
        }
        return $ret;
    }
}
