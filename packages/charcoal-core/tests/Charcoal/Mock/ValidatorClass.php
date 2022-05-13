<?php

namespace Charcoal\Tests\Mock;

// From 'charcoal-core'
use Charcoal\Validator\AbstractValidator;

/**
 * Concrete implementation of AbstractValidator for Unit Tests.
 */
class ValidatorClass extends AbstractValidator
{
    /**
     * @return boolean
     */
    public function validate()
    {
        return true;
    }
}
