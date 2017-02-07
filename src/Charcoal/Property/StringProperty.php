<?php

namespace Charcoal\Property;

use \Exception;
use \InvalidArgumentException;
use \PDO;

use \Charcoal\Core\StringFormat;

use \Charcoal\Property\AbstractProperty;
use \Charcoal\Property\SelectablePropertyInterface;
use \Charcoal\Property\SelectablePropertyTrait;

/**
 * String Property
 */
class StringProperty extends AbstractProperty implements SelectablePropertyInterface
{
    use SelectablePropertyTrait;

    const DEFAULT_MIN_LENGTH = 0;
    const DEFAULT_MAX_LENGTH = 255;
    const DEFAULT_REGEXP = '';
    const DEFAULT_ALLOW_EMPTY = true;

    /**
     * @var int $minLength
     */
    private $minLength;

    /**
     * @var int $maxLength
     */
    private $maxLength;

    /**
     * Defines a validation regular expression for this string.
     * @var string $regexp
     */
    private $regexp;

    /**
     * @var boolean $allowEmpty
     */
    private $allowEmpty;

    /**
     * @return string
     */
    public function type()
    {
        return 'string';
    }

    /**
     * @param  mixed $val     The value to to convert for display.
     * @param  array $options Optional display options.
     * @return string
     */
    public function displayVal($val, array $options = [])
    {
        if ($val === null) {
            return '';
        }

        if ($this->l10n()) {
            $propertyValue = $this->l10nVal($val, $options);
            if ($propertyValue === null) {
                return '';
            }
        } else {
            $propertyValue = $val;
        }

        if ($this->multiple()) {
            $separator = $this->multipleSeparator();
            if (!is_array($propertyValue)) {
                $propertyValue = explode($separator, $propertyValue);
            }

            $values = [];
            foreach ($propertyValue as $val) {
                $values[] = $this->valLabel($val);
            }

            if ($separator === ',') {
                $separator = ', ';
            }

            $propertyValue = implode($separator, $values);
        } else {
            $propertyValue = $this->valLabel((string)$propertyValue);
        }

        return $propertyValue;
    }

    /**
     * Attempt to get the label from choices. Otherwise, return the raw value.
     *
     * @param string $val The value to retrieve the label of.
     * @return string
     */
    protected function valLabel($val)
    {
        if ($this->hasChoice($val)) {
            $choice = $this->choice($val);
            return $choice['label'];
        } else {
            return $val;
        }
    }

    /**
     * @param integer $maxLength The max length allowed.
     * @throws InvalidArgumentException If the parameter is not an integer or < 0.
     * @return StringProperty Chainable
     */
    public function setMaxLength($maxLength)
    {
        if (!is_integer($maxLength)) {
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
     * @return integer
     */
    public function maxLength()
    {
        if ($this->maxLength === null) {
            $this->maxLength = $this->defaultMaxLength();
        }
        return $this->maxLength;
    }

    /**
     * @return integer
     */
    public function defaultMaxLength()
    {
        return 255;
    }

    /**
     * @param integer $minLength The minimum length allowed.
     * @throws InvalidArgumentException If the parameter is not an integer or < 0.
     * @return StringProperty Chainable
     */
    public function setMinLength($minLength)
    {
        if (!is_integer($minLength)) {
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
     * @return integer
     */
    public function minLength()
    {
        if ($this->minLength === null) {
            $this->minLength = self::DEFAULT_MIN_LENGTH;
        }
        return $this->minLength;
    }

    /**
     * @param string $regexp The allowed regular expression.
     * @throws InvalidArgumentException If the parameter is not a string.
     * @return StringProperty Chainable
     */
    public function setRegexp($regexp)
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
     * @return string
     */
    public function regexp()
    {
        if ($this->regexp === null) {
            $this->regexp = self::DEFAULT_REGEXP;
        }
        return $this->regexp;
    }

    /**
     * @param boolean $allowEmpty The allow empty flag.
     * @return StringProperty Chainable
     */
    public function setAllowEmpty($allowEmpty)
    {
        $this->allowEmpty = !!$allowEmpty;
        return $this;
    }

    /**
     * @return boolean
     */
    public function allowEmpty()
    {
        if ($this->allowEmpty === null) {
            $this->allowEmpty = self::DEFAULT_ALLOW_EMPTY;
        }
        return $this->allowEmpty;
    }

    /**
     * Retrieve the length of the string.
     *
     * @todo Returns the string length based on the displayed value.
     *     To be determined how to best handle multilingual and multiton values.
     * @throws Exception If the value is not a string.
     * @return integer
     */
    public function length()
    {
        $val = $this->displayVal($this->val());

        if ($val === null) {
            return 0;
        }

        if (!is_string($val)) {
            throw new Exception(
                'Unable to determine string length: value is not a string'
            );
        }

        return mb_strlen($val);
    }

    /**
     * @return array
     */
    public function validationMethods()
    {
        $parentMethods = parent::validationMethods();
        return array_merge($parentMethods, [
            'maxLength',
            'minLength',
            'regexp',
            'allowEmpty'
        ]);
    }

    /**
     * @return boolean
     * @todo Support `multiple` / `l10n`
     */
    public function validateMaxLength()
    {
        $val = $this->val();

        if ($val === null && $this->allowNull()) {
            return true;
        }

        $maxLength = $this->maxLength();
        if ($maxLength == 0) {
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
                $valid = (mb_strlen($v) <= $maxLength);
                if (!$valid) {
                    $this->validator()->error('Maximum length error', 'maxLength');
                    return $valid;
                }
            }
        }

        return $valid;
    }

    /**
     * @return boolean
     * @todo Support `multiple` / `l10n`
     */
    public function validateMinLength()
    {
        $val = $this->val();

        if ($val === null) {
            return $this->allowNull();
        }

        $minLength = $this->minLength();
        if ($minLength == 0) {
            return true;
        }

        if ($val === '' && $this->allowEmpty()) {
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
                $valid = (mb_strlen($v) >= $minLength);
                if (!$valid) {
                    $this->validator()->error('Minimum length error', 'minLength');
                    return $valid;
                }
            }
        }

        return $valid;
    }

    /**
     * @return boolean
     */
    public function validateRegexp()
    {
        $val = $this->val();

        $regexp = $this->regexp();
        if ($regexp == '') {
            return true;
        }

        $valid = !!preg_match($regexp, $val);
        if (!$valid) {
            $this->validator()->error('Regexp error', 'regexp');
        }

        return $valid;
    }

    /**
     * @return boolean
     */
    public function validateAllowEmpty()
    {
        $val = $this->val();

        if (($val === null || $val === '') && !$this->allowEmpty()) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @return string
     */
    public function sqlExtra()
    {
        return '';
    }

    /**
     * Get the SQL type (Storage format)
     *
     * Stored as `VARCHAR` for maxLength under 255 and `TEXT` for other, longer strings
     *
     * @return string The SQL type
     */
    public function sqlType()
    {
        // Multiple strings are always stored as TEXT because they can hold multiple values
        if ($this->multiple()) {
            return 'TEXT';
        }

        $maxLength = $this->maxLength();
        // VARCHAR or TEXT, depending on length
        if ($maxLength <= 255 && $maxLength != 0) {
            return 'VARCHAR('.$maxLength.')';
        } else {
            return 'TEXT';
        }
    }

    /**
     * @return integer
     */
    public function sqlPdoType()
    {
        return PDO::PARAM_STR;
    }
}
