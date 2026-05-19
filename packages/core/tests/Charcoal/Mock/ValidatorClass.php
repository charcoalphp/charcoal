<?php

declare(strict_types=1);

namespace Charcoal\Tests\Mock;

// From 'charcoal-core'
use Charcoal\Validator\AbstractValidator;

/**
 * Concrete implementation of AbstractValidator for Unit Tests.
 */
class ValidatorClass extends AbstractValidator
{
    public function validate(): bool
    {
        return true;
    }
}
