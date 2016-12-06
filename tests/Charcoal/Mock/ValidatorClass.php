<?php

namespace Charcoal\Tests\Mock;

use \Charcoal\Validator\AbstractValidator as AbstractValidator;

/**
 * Concrete implementation of AbstractValidator for Unit Tests.
 */
class ValidatorClass extends AbstractValidator
{
    public function validate()
    {
        return true;
    }
}
