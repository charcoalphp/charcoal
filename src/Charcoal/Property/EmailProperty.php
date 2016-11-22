<?php

namespace Charcoal\Property;

// Local namespace dependencies
use \Charcoal\Property\StringProperty;

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
     * @return integer
     */
    public function maxLength()
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
            'email'
        ]);
    }

    /**
     * @return boolean
     */
    public function validateEmail()
    {
        $val = $this->val();
        $emailValid = filter_var($val, FILTER_VALIDATE_EMAIL);
        return !!$emailValid;
    }
}
