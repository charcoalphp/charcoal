<?php

namespace Charcoal\Property;

use PDO;
use DateTime;
use DateTimeInterface;
use Exception;
use InvalidArgumentException;

// From 'charcoal-property'
use Charcoal\Property\AbstractProperty;

/**
 * Date/Time Property
 */
class DateTimeProperty extends AbstractProperty
{
    const DEFAULT_MIN = null;
    const DEFAULT_MAX = null;
    const DEFAULT_FORMAT = 'Y-m-d H:i:s';

    /**
     * @var DateTimeInterface|null
     */
    private $min = self::DEFAULT_MIN;

    /**
     * @var DateTimeInterface|null
     */
    private $max = self::DEFAULT_MAX;

    /**
     * @var string
     */
    private $format = self::DEFAULT_FORMAT;

    /**
     * @return string
     */
    public function type()
    {
        return 'date-time';
    }

    /**
     * Ensure multiple can not be true for DateTime property.
     *
     * @see AbstractProperty::setMultiple()
     *
     * @param  boolean $multiple Multiple flag.
     * @throws InvalidArgumentException If the multiple argument is true (must be false).
     * @return self
     */
    public function setMultiple($multiple)
    {
        $multiple = !!$multiple;
        if ($multiple === true) {
            throw new InvalidArgumentException(
                'Multiple can not be TRUE for date/time property.'
            );
        }
        return $this;
    }

    /**
     * Multiple is always false for DateTime property.
     *
     * @see AbstractProperty::getMultiple()
     *
     * @return boolean
     */
    public function getMultiple()
    {
        return false;
    }

    /**
     * Ensure `DateTime` object in val.
     *
     * @see AbstractProperty::parseOne()
     * @see AbstractProperty::parseVal()
     *
     * @param  string|DateTimeInterface $val The value to set.
     * @return DateTimeInterface|null
     */
    public function parseOne($val)
    {
        return $this->dateTimeVal($val);
    }

    /**
     * Convert `DateTime` to input-friendly string.
     *
     * @see AbstractProperty::inputVal()
     *
     * @param  mixed $val     The value to to convert for input.
     * @param  array $options Unused, optional options.
     * @throws Exception If the date/time is invalid.
     * @return string|null
     */
    public function inputVal($val, array $options = [])
    {
        unset($options);
        $val = $this->dateTimeVal($val);

        if ($val instanceof DateTimeInterface) {
            return $val->format('Y-m-d H:i:s');
        } else {
            return '';
        }
    }

    /**
     * Convert `DateTime` to SQL-friendly string.
     *
     * @see StorablePropertyTrait::storageVal()
     *
     * @param  string|DateTime $val Optional. Value to convert to storage format.
     * @throws Exception If the date/time is invalid.
     * @return string|null
     */
    public function storageVal($val)
    {
        $val = $this->dateTimeVal($val);

        if ($val instanceof DateTimeInterface) {
            return $val->format('Y-m-d H:i:s');
        }

        if ($this['allowNull']) {
            return null;
        }

        throw new Exception(
            'Invalid date/time value. Must be a DateTimeInterface instance.'
        );
    }

    /**
     * Format a date/time object to string.
     *
     * @see AbstractProperty::displayVal()
     *
     * @param  mixed $val     The value to to convert for display.
     * @param  array $options Optional display options.
     * @return string
     */
    public function displayVal($val, array $options = [])
    {
        $val = $this->dateTimeVal($val);
        if ($val === null) {
            return '';
        }

        if (isset($options['format'])) {
            $format = $options['format'];
        } else {
            $format = $this->getFormat();
        }

        return $val->format($format);
    }

    /**
     * @param  string|DateTime|null $min The minimum allowed value.
     * @throws InvalidArgumentException If the date/time is invalid.
     * @return self
     */
    public function setMin($min)
    {
        try {
            $this->min = $this->dateTimeVal($min);
            return $this;
        } catch (Exception $e) {
            throw new InvalidArgumentException('Invalid minimum date/time', 0, $e);
        }
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getMin()
    {
        return $this->min;
    }

    /**
     * @param  string|DateTime|null $max The maximum allowed value.
     * @throws InvalidArgumentException If the date/time is invalid.
     * @return self
     */
    public function setMax($max)
    {
        try {
            $this->max = $this->dateTimeVal($max);
            return $this;
        } catch (Exception $e) {
            throw new InvalidArgumentException('Invalid maximum date/time', 0, $e);
        }
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * @param  string|null $format The date format.
     * @throws InvalidArgumentException If the format is not a string.
     * @return DateTimeProperty Chainable
     */
    public function setFormat($format)
    {
        if ($format === null) {
            $format = '';
        }
        if (!is_string($format)) {
            throw new InvalidArgumentException(
                'Format must be a string'
            );
        }
        $this->format = $format;
        return $this;
    }

    /**
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @return array
     */
    public function validationMethods()
    {
        $parentMethods = parent::validationMethods();

        return array_merge($parentMethods, [
            'min',
            'max',
        ]);
    }

    /**
     * @return boolean
     */
    public function validateMin()
    {
        $min = $this->getMin();
        if (!$min) {
            return true;
        }
        $valid = ($this->val() >= $min);
        if ($valid === false) {
            $this->validator()->error('The date is smaller than the minimum value', 'min');
        }
        return $valid;
    }

    /**
     * @return boolean
     */
    public function validateMax()
    {
        $max = $this->getMax();
        if (!$max) {
            return true;
        }
        $valid = ($this->val() <= $max);
        if ($valid === false) {
            $this->validator()->error('The date is bigger than the maximum value', 'max');
        }
        return $valid;
    }

    /**
     * @see StorablePropertyTrait::sqlType()
     * @return string
     */
    public function sqlType()
    {
        return 'DATETIME';
    }

    /**
     * @see StorablePropertyTrait::sqlPdoType()
     * @return integer
     */
    public function sqlPdoType()
    {
        return PDO::PARAM_STR;
    }

    /**
     * @param  mixed $val Value to convert to DateTime.
     * @throws InvalidArgumentException If the value is not a valid datetime.
     * @return DateTimeInterface|null
     */
    private function dateTimeVal($val)
    {
        if ($val === null ||
            (is_string($val) && ! strlen(trim($val))) ||
            (is_array($val) && ! count(array_filter($val, 'strlen')))
        ) {
            return null;
        }

        if (is_int($val) && $this->isValidTimeStamp($val)) {
            $dateTime = new DateTime();
            $val = $dateTime->setTimestamp($val);
        }

        if (is_string($val)) {
            $val = new DateTime($val);
        }

        if (!($val instanceof DateTimeInterface)) {
            throw new InvalidArgumentException(
                'Value must be a valid date/time'
            );
        }

        return $val;
    }

    /**
     * @param  integer|string $timestamp Timestamp.
     * @return boolean
     */
    private function isValidTimeStamp($timestamp)
    {
        return (is_int($timestamp))
            && ($timestamp <= PHP_INT_MAX)
            && ($timestamp >= ~PHP_INT_MAX);
    }
}
