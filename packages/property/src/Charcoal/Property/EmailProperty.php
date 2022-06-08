<?php

namespace Charcoal\Property;

// From 'charcoal-property'
use Charcoal\Property\StringProperty;

/**
 * Email Property. Email address.
 */
class EmailProperty extends StringProperty
{
    /**
     * @return string
     */
    public function type()
    {
        return 'email';
    }

    /**
     * Email's maximum length is defined in RFC-3696 (+ errata) as 254 characters.
     *
     * This overrides PropertyString's maxLength() to ensure compliance with the email standards.
     *
     * @return integer
     */
    public function getMaxLength()
    {
        return 254;
    }

    /**
     * @return array
     */
    public function validationMethods()
    {
        $parentMethods = parent::validationMethods();

        return array_merge($parentMethods, [
            'email',
        ]);
    }

    /**
     * @return boolean
     */
    public function validateEmail()
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
    public function parseOne($val)
    {
        return filter_var(strip_tags($val), FILTER_SANITIZE_EMAIL);
    }
}
