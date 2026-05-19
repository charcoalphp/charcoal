<?php

namespace Charcoal\Property;

use PDO;
use InvalidArgumentException;
// From 'charcoal-translator'
use Charcoal\Translator\Translation;
// From 'charcoal-property'
use Charcoal\Property\AbstractProperty;
use Charcoal\Property\SelectablePropertyInterface;
use Charcoal\Property\SelectablePropertyTrait;

/**
 * String Property
 */
class StringProperty extends AbstractProperty implements SelectablePropertyInterface
{
    use SelectablePropertyTrait;

    public const DEFAULT_MIN_LENGTH  = 0;
    public const DEFAULT_MAX_LENGTH  = 255;
    public const DEFAULT_REGEXP      = '';
    public const DEFAULT_ALLOW_EMPTY = true;
    public const DEFAULT_ALLOW_HTML  = false;

    /**
     * The minimum number of characters allowed.
     */
    private ?int $minLength = null;

    /**
     * The maximum number of characters allowed.
     */
    private ?int $maxLength = null;

    /**
     * The regular expression the value is checked against.
     */
    private ?string $regexp = null;

    /**
     * Whether the value is allowed to be empty.
     */
    private bool $allowEmpty = self::DEFAULT_ALLOW_EMPTY;

    private bool $allowHtml = self::DEFAULT_ALLOW_HTML;

    public function type(): string
    {
        return 'string';
    }

    /**
     * Format the given value for display.
     *
     * @param  mixed $val     The value to to convert for display.
     * @param  array $options Optional display options.
     */
    #[\Override]
    public function displayVal($val, array $options = []): string
    {
        if ($val === null || $val === '') {
            return '';
        }

        /** Parse multilingual values */
        if ($this['l10n']) {
            $propertyValue = $this->l10nVal($val, $options);
            if ($propertyValue === null) {
                return '';
            }
        } elseif ($val instanceof Translation) {
            $propertyValue = strval($val);
        } else {
            $propertyValue = $val;
        }

        /** Parse multiple values / ensure they are of array type. */
        if ($this['multiple'] && !is_array($propertyValue)) {
            $propertyValue = $this->parseValAsMultiple($propertyValue);
        }

        if (is_array($propertyValue)) {
            $separator = $this->multipleSeparator();
            if ($separator === ',') {
                $separator = ', ';
            }

            foreach ($propertyValue as &$value) {
                $value = strval($this->valLabel($value, $options));
            }

            $propertyValue = implode($separator, $propertyValue);
        } else {
            $propertyValue = strval($this->valLabel($propertyValue, $options));
        }

        return $propertyValue;
    }

    /**
     * Set the maximum number of characters allowed.
     *
     * @param  integer $maxLength The max length allowed.
     * @throws InvalidArgumentException If the parameter is not an integer or < 0.
     */
    public function setMaxLength($maxLength): static
    {
        if (!is_int($maxLength)) {
            throw new InvalidArgumentException(
                'Max length must be an integer.'
            );
        }

        if ($maxLength < 0) {
            throw new InvalidArgumentException(
                'Max length must be a positive integer (>=0).'
            );
        }

        $this->maxLength = $maxLength;

        return $this;
    }

    /**
     * Retrieve the maximum number of characters allowed.
     */
    public function getMaxLength(): int
    {
        if ($this->maxLength === null) {
            $this->maxLength = $this->defaultMaxLength();
        }

        return $this->maxLength;
    }

    /**
     * Retrieve the default maximum number of characters allowed.
     */
    public function defaultMaxLength(): int
    {
        return self::DEFAULT_MAX_LENGTH;
    }

    /**
     * Set the minimum number of characters allowed.
     *
     * @param integer $minLength The minimum length allowed.
     * @throws InvalidArgumentException If the parameter is not an integer or < 0.
     */
    public function setMinLength($minLength): static
    {
        if (!is_int($minLength)) {
            throw new InvalidArgumentException(
                'Min length must be an integer.'
            );
        }

        if ($minLength < 0) {
            throw new InvalidArgumentException(
                'Min length must be a positive integer (>=0).'
            );
        }

        $this->minLength = $minLength;

        return $this;
    }

    /**
     * Retrieve the minimum number of characters allowed.
     */
    public function getMinLength(): int
    {
        if ($this->minLength === null) {
            $this->minLength = $this->defaultMinLength();
        }

        return $this->minLength;
    }

    /**
     * Retrieve the default minimum number of characters allowed.
     */
    public function defaultMinLength(): int
    {
        return 0;
    }

    /**
     * Set the regular expression to check the value against.
     *
     * @param  string $regexp A regular expression.
     * @throws InvalidArgumentException If the parameter is not a string.
     */
    public function setRegexp($regexp): static
    {
        if (!is_string($regexp)) {
            throw new InvalidArgumentException(
                'Regular expression must be a string.'
            );
        }

        $this->regexp = $regexp;

        return $this;
    }

    /**
     * Retrieve the regular expression to check the value against.
     */
    public function getRegexp(): string
    {
        if ($this->regexp === null) {
            $this->regexp = self::DEFAULT_REGEXP;
        }

        return $this->regexp;
    }

    /**
     * Set whether the value is allowed to be empty.
     *
     * @param  boolean $allowEmpty The allow empty flag.
     */
    public function setAllowEmpty($allowEmpty): static
    {
        $this->allowEmpty = (bool) $allowEmpty;

        return $this;
    }

    /**
     * Determine if the value is allowed to be empty.
     */
    public function getAllowEmpty(): bool
    {
        return $this->allowEmpty;
    }

    /**
     * Set whether the value is allowed to contain HTML.
     *
     * @param boolean $allowHtml The allow HTML flag.
     */
    public function setAllowHtml($allowHtml): static
    {
        $this->allowHtml = (bool) $allowHtml;

        return $this;
    }

    public function getAllowHtml(): bool
    {
        return $this->allowHtml;
    }

    /**
     * Retrieve the length of the string.
     *
     * Note:
     * 1. If the property is multilingual, the value for the current locale is evaluated.
     * 2. If the property is a multiton, all values are counted together.
     *
     * @todo   Support `multiple` / `l10n`
     */
    public function length(): int
    {
        $val = $this->displayVal($this->val());

        return mb_strlen($val);
    }

    /**
     * The property's default validation methods.
     *
     * @return string[]
     */
    #[\Override]
    public function validationMethods(): array
    {
        $parentMethods = parent::validationMethods();

        return array_merge($parentMethods, [
            'maxLength',
            'minLength',
            'regexp',
            'allowEmpty',
        ]);
    }

    /**
     * Validate if the property's value exceeds the maximum length.
     *
     * @todo   Support `multiple` / `l10n`
     * @return boolean
     */
    public function validateMaxLength()
    {
        $val = $this->val();

        if ($val === null && $this['allowNull']) {
            return true;
        }

        $maxLength = $this->getMaxLength();
        if ($maxLength === 0) {
            return true;
        }

        if (is_string($val)) {
            $valid = (mb_strlen($val) <= $maxLength);
            if (!$valid) {
                $this->validator()->error('Maximum length error', 'maxLength');
            }
        } else {
            $valid = true;
            foreach ($val as $v) {
                $valid = (mb_strlen((string) $v) <= $maxLength);
                if (!$valid) {
                    $this->validator()->error('Maximum length error', 'maxLength');
                    return $valid;
                }
            }
        }

        return $valid;
    }

    /**
     * Validate if the property's value satisfies the minimum length.
     *
     * @todo   Support `multiple` / `l10n`
     * @return boolean
     */
    public function validateMinLength()
    {
        $val = $this->val();

        if ($val === null) {
            return $this['allowNull'];
        }

        $minLength = $this['minLength'];
        if ($minLength == 0) {
            return true;
        }

        if ($val === '' && $this['allowEmpty']) {
            // Don't check empty string if they are allowed
            return true;
        }

        if (is_string($val)) {
            $valid = (mb_strlen($val) >= $minLength);
            if (!$valid) {
                $this->validator()->error('Minimum length error', 'minLength');
            }
        } else {
            $valid = true;
            foreach ($val as $v) {
                $valid = (mb_strlen((string) $v) >= $minLength);
                if (!$valid) {
                    $this->validator()->error('Minimum length error', 'minLength');
                    return $valid;
                }
            }
        }

        return $valid;
    }

    /**
     * Validate if the property's value matches the regular expression.
     *
     * @return boolean
     */
    public function validateRegexp()
    {
        $val = $this->val();

        $regexp = $this['regexp'];
        if ($regexp == '') {
            return true;
        }

        $valid = (bool) preg_match($regexp, (string) $val);
        if (!$valid) {
            $this->validator()->error('Regexp error', 'regexp');
        }

        return $valid;
    }

    /**
     * Validate if the property's value is allowed to be empty.
     */
    public function validateAllowEmpty(): bool
    {
        $val = $this->val();
        if (!$this['allowEmpty'] && empty($val) && !is_numeric($val)) {
            $this->validator()->error('Value can not be empty.', 'allowEmpty');

            return false;
        }

        return true;
    }

    /**
     * Parse a value. (From `AbstractProperty`).
     *
     * Strip HTML if it is not allowed.
     *
     * @see AbstractProperty::parseVal()
     *
     * @param mixed $val A single value to parse.
     * @return mixed The parsed value.
     */
    #[\Override]
    public function parseOne($val)
    {
        if ($this['allowHtml'] === false && is_string($val)) {
            return strip_tags($val);
        }

        return $val;
    }

    /**
     * Get the SQL type (Storage format)
     *
     * Stored as `VARCHAR` for maxLength under 255 and `TEXT` for other, longer strings
     *
     * @see StorablePropertyTrait::sqlType()
     * @return string The SQL type
     */
    public function sqlType(): string
    {
        // Multiple strings are always stored as TEXT because they can hold multiple values
        if ($this['multiple']) {
            return 'TEXT';
        }

        $maxLength = $this['maxLength'];
        // VARCHAR or TEXT, depending on length
        if ($maxLength <= 255 && $maxLength != 0) {
            return 'VARCHAR(' . $maxLength . ')';
        } else {
            return 'TEXT';
        }
    }

    /**
     * @see StorablePropertyTrait::sqlPdoType()
     */
    public function sqlPdoType(): int
    {
        return PDO::PARAM_STR;
    }

    /**
     * Attempts to return the label for a given choice.
     *
     * @param  string $val  The value to retrieve the label of.
     * @param  mixed  $lang The language to return the label in.
     * @return string Returns the label. Otherwise, returns the raw value.
     */
    protected function valLabel($val, $lang = null)
    {
        if (is_string($val) && $this->hasChoice($val)) {
            $choice = $this->choice($val);
            return $this->l10nVal($choice['label'], $lang);
        } else {
            return $val;
        }
    }
}
