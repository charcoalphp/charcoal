<?php

namespace Charcoal\Property;

// From 'charcoal-property'
use Charcoal\Property\StringProperty;

/**
 * Telephone Property
 *
 * Phone numbers.
 */
class PhoneProperty extends StringProperty
{
    /**
     * @return string
     */
    public function type()
    {
        return 'phone';
    }

    /**
     * Set StringProperty's `defaultMaxLength` to 16 for phone numbers.
     *
     * @see StringProperty::defaultMaxLength()
     * @return integer
     */
    public function defaultMaxLength()
    {
        return 16;
    }

    /**
     * Sanitize a phone value by removing all non-digit characters.
     *
     * @param mixed $val Optional. The value to sanitize. If none provided, use `val()`.
     * @return string
     */
    public function sanitize($val)
    {
        return preg_replace('/[^0-9]/', '', $val);
    }

    /**
     * @see AbstractProperty::displayVal()
     *
     * @param  mixed $val     The value to to convert for display.
     * @param  array $options Unused display options.
     * @return string
     */
    public function displayVal($val, array $options = [])
    {
        unset($options);

        $val = $this->sanitize($val);

        if (strlen($val) == 10) {
            $areaCode = substr($val, 0, 3);
            $part1 = substr($val, 3, 3);
            $part2 = substr($val, 6, 4);
            return '('.$areaCode.') '.$part1.'-'.$part2;
        } else {
            return $val;
        }
    }
}
