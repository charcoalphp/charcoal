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
    public const DEFAULT_MIN = null;
    public const DEFAULT_MAX = null;
    public const DEFAULT_FORMAT = 'Y-m-d H:i:s';

    private ?\DateTimeInterface $min = self::DEFAULT_MIN;

    private ?\DateTimeInterface $max = self::DEFAULT_MAX;

    private string $format = self::DEFAULT_FORMAT;

    public function type(): string
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
     */
    #[\Override]
    public function setMultiple($multiple): static
    {
        $multiple = (bool) $multiple;
        if ($multiple) {
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
     */
    #[\Override]
    public function getMultiple(): bool
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
     */
    #[\Override]
    public function parseOne($val): ?\DateTimeInterface
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
     */
    #[\Override]
    public function inputVal($val, array $options = []): string
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
     */
    #[\Override]
    public function storageVal($val): ?string
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
     */
    #[\Override]
    public function displayVal($val, array $options = []): string
    {
        $val = $this->dateTimeVal($val);
        if (!$val instanceof \DateTimeInterface) {
            return '';
        }

        $format = $options['format'] ?? $this->getFormat();

        return $val->format($format);
    }

    /**
     * @param  string|DateTime|null $min The minimum allowed value.
     * @throws InvalidArgumentException If the date/time is invalid.
     */
    public function setMin($min): static
    {
        try {
            $this->min = $this->dateTimeVal($min);
            return $this;
        } catch (Exception $e) {
            throw new InvalidArgumentException('Invalid minimum date/time', 0, $e);
        }
    }

    public function getMin(): ?\DateTimeInterface
    {
        return $this->min;
    }

    /**
     * @param  string|DateTime|null $max The maximum allowed value.
     * @throws InvalidArgumentException If the date/time is invalid.
     */
    public function setMax($max): static
    {
        try {
            $this->max = $this->dateTimeVal($max);
            return $this;
        } catch (Exception $e) {
            throw new InvalidArgumentException('Invalid maximum date/time', 0, $e);
        }
    }

    public function getMax(): ?\DateTimeInterface
    {
        return $this->max;
    }

    /**
     * @param  string|null $format The date format.
     * @throws InvalidArgumentException If the format is not a string.
     * @return DateTimeProperty Chainable
     */
    public function setFormat($format): static
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

    public function getFormat(): string
    {
        return $this->format;
    }

    #[\Override]
    public function validationMethods(): array
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
        if (!$min instanceof \DateTimeInterface) {
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
        if (!$max instanceof \DateTimeInterface) {
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
     */
    public function sqlType(): string
    {
        return 'DATETIME';
    }

    /**
     * @see StorablePropertyTrait::sqlPdoType()
     */
    public function sqlPdoType(): int
    {
        return PDO::PARAM_STR;
    }

    /**
     * @param  mixed $val Value to convert to DateTime.
     * @throws InvalidArgumentException If the value is not a valid datetime.
     */
    private function dateTimeVal($val): ?\DateTimeInterface
    {
        if (
            $val === null ||
            (is_string($val) && ! strlen(trim($val))) ||
            (is_array($val) && ! count(array_filter($val, strlen(...))))
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
     */
    private function isValidTimeStamp(int $timestamp): bool
    {
        return (is_int($timestamp))
            && ($timestamp <= PHP_INT_MAX)
            && ($timestamp >= ~PHP_INT_MAX);
    }
}
