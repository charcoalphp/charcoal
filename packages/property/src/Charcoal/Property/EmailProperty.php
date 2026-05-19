<?php

namespace Charcoal\Property;

// From 'charcoal-property'
use Charcoal\Property\StringProperty;

/**
 * Email Property. Email address.
 */
class EmailProperty extends StringProperty
{
    #[\Override]
    public function type(): string
    {
        return 'email';
    }

    /**
     * Email's maximum length is defined in RFC-3696 (+ errata) as 254 characters.
     *
     * This overrides PropertyString's maxLength() to ensure compliance with the email standards.
     */
    #[\Override]
    public function getMaxLength(): int
    {
        return 254;
    }

    #[\Override]
    public function validationMethods(): array
    {
        $parentMethods = parent::validationMethods();

        return array_merge($parentMethods, [
            'email',
        ]);
    }

    public function validateEmail(): bool
    {
        if ($this['allowNull'] && !$this['required']) {
            return true;
        }

        $val = $this->val();
        if (!filter_var($val, FILTER_VALIDATE_EMAIL)) {
            $this->validator()->error('Value is not an email address.', 'email');
            return false;
        }

        return true;
    }

    /**
     * @see AbstractProperty::parseOne()
     * @see AbstractProperty::parseVal()
     *
     * @param  mixed $val A single value to parse.
     * @return string
     */
    #[\Override]
    public function parseOne($val): string|false
    {
        return filter_var(strip_tags((string) $val), FILTER_SANITIZE_EMAIL);
    }
}
