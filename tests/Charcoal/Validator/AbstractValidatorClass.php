<?php

namespace Charcoal\Tests\Validator;

use \Charcoal\Validator\AbstractValidator as AbstractValidator;

/**
 * Concrete implementation of AbstractValidator for Unit Tests.
 */
class AbstractValidatorClass extends AbstractValidator
{
    public function validate()
    {
        return true;
    }
}
