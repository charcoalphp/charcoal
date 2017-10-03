<?php

namespace Charcoal\Tests\Mock;

// From 'charcoal-core'
use Charcoal\Validator\ValidatableInterface;
use Charcoal\Validator\ValidatableTrait;

/**
 * Concrete implementation of AbstractValidator for Unit Tests.
 */
class ValidatableClass implements ValidatableInterface
{
    use ValidatableTrait;

    public $foo = 'bar';

    public function createValidator($data = null)
    {
        $v = new ValidatorClass();
        if ($data !== null) {
            $v->setData($data);
        }
        return $v;
    }
}
