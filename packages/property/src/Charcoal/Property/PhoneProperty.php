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
    #[\Override]
    public function type(): string
    {
        return 'phone';
    }

    /**
     * Set StringProperty's `defaultMaxLength` to 16 for phone numbers.
     *
     * @see StringProperty::defaultMaxLength()
     */
    #[\Override]
    public function defaultMaxLength(): int
    {
        return 16;
    }

    /**
     * Sanitize a phone value by removing all non-digit characters.
     *
     * @param mixed $val Optional. The value to sanitize. If none provided, use `val()`.
     * @return string
     */
    public function sanitize($val): ?string
    {
        return preg_replace('/[^0-9]/', '', (string) $val);
    }

    /**
     * @see AbstractProperty::displayVal()
     *
     * @param  mixed $val     The value to to convert for display.
     * @param  array $options Unused display options.
     */
    #[\Override]
    public function displayVal($val, array $options = []): string
    {
        unset($options);

        $val = $this->sanitize($val);

        if (strlen((string) $val) === 10) {
            $areaCode = substr((string) $val, 0, 3);
            $part1 = substr((string) $val, 3, 3);
            $part2 = substr((string) $val, 6, 4);
            return '(' . $areaCode . ') ' . $part1 . '-' . $part2;
        } else {
            return $val;
        }
    }
}
