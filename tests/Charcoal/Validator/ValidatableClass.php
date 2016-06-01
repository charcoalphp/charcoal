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

    public function createValidator($data = null)
    {
        include_once 'AbstractValidatorClass.php';
        $v = new AbstractValidatorClass();
        if ($data !== null) {
            $v->setData($data);
        }
        return $v;
    }
}
