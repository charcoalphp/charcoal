<?php

namespace Charcoal\Tests\Validator;

use \Charcoal\Validator\ValidatableInterface as ValidatableInterface;
use \Charcoal\Validator\ValidatableTrait as ValidatableTrait;

/**
* Concrete implementation of AbstractValidator for Unit Tests.
*/
class ValidatableClass implements ValidatableInterface
{
    use ValidatableTrait;

    public $foo = 'bar';

    public function create_validator($data = null)
    {
        include_once 'AbstractValidatorClass.php';
        $v = new AbstractValidatorClass();
        if ($data !== null) {
            $v->set_data($data);
        }
        return $v;
    }
}
