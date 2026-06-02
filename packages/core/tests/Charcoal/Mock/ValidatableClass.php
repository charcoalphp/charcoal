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

    /**
     * @var string
     */
    public $foo = 'bar';

    /**
     * @param  array|null $data Validator data.
     */
    public function createValidator(?array $data = null): \Charcoal\Tests\Mock\ValidatorClass
    {
        $v = new ValidatorClass();
        if ($data !== null) {
            $v->setData($data);
        }
        return $v;
    }
}
