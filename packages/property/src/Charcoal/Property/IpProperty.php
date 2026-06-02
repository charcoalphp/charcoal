<?php

namespace Charcoal\Property;

use PDO;
use InvalidArgumentException;
// From 'charcoal-property'
use Charcoal\Property\AbstractProperty;

/**
 * IP Property (IPv4).
 */
class IpProperty extends AbstractProperty
{
    public const STORAGE_MODE_STRING = 'string';
    public const STORAGE_MODE_INT = 'int';

    public const DEFAULT_STORAGE_MODE = self::STORAGE_MODE_STRING;

    /**
     * The storage mode can be either "string" (default) or "int".
     */
    private string $storageMode = self::DEFAULT_STORAGE_MODE;

    /**
     * Retrieve the property type.
     */
    public function type(): string
    {
        return 'ip';
    }

    /**
     * Ensure multiple can not be TRUE for ID property.
     *
     * @param boolean $multiple The multiple flag.
     * @return IdProperty Chainable
     *@throws InvalidArgumentException If the multiple argument is TRUE (must be FALSE).
     * @see    AbstractProperty::setMultiple()
     */
    #[\Override]
    public function setMultiple(bool $multiple): static
    {
        $multiple = (bool)$multiple;

        if ($multiple) {
            throw new InvalidArgumentException(
                'The ID property does not support multiple values.'
            );
        }

        return $this;
    }

    /**
     * Multiple is always FALSE for ID property.
     *
     * @see    AbstractProperty::getMultiple()
     */
    #[\Override]
    public function getMultiple(): bool
    {
        return false;
    }

    /**
     * Ensure l10n can not be TRUE for IP property.
     *
     * @param boolean $l10n The l10n, or "translatable" flag.
     * @return IdProperty Chainable
     *@throws InvalidArgumentException If the L10N argument is TRUE (must be FALSE).
     * @see    AbstractProperty::setL10n()
     */
    #[\Override]
    public function setL10n(bool $l10n): static
    {
        $l10n = (bool)$l10n;

        if ($l10n) {
            throw new InvalidArgumentException(
                'The ID property is not translatable.'
            );
        }

        return $this;
    }

    /**
     * L10N is always FALSE for IP property.
     *
     * @see    AbstractProperty::getL10n()
     */
    #[\Override]
    public function getL10n(): bool
    {
        return false;
    }

    /**
     * @param string $mode Either "string" or "int".
     * @throws InvalidArgumentException If the storage mode is invalid.
     */
    public function setStorageMode($mode): static
    {
        $validModes = [
            self::STORAGE_MODE_STRING,
            self::STORAGE_MODE_INT
        ];
        if (!in_array($mode, $validModes)) {
            throw new InvalidArgumentException(
                'Can not set IP storage mode: invalid mode.'
            );
        }
        $this->storageMode = $mode;
        return $this;
    }

    public function getStorageMode(): string
    {
        return $this->storageMode;
    }

    /**
     * Get the IP value as a long integer.
     *
     * @param mixed $val The value to convert (if necessary) to integer.
     * @return integer
     */
    public function intVal($val): int|false
    {
        if (is_numeric($val)) {
            return (int)$val;
        } else {
            return ip2long($val);
        }
    }

    /**
     * Get the IP value as an string (IPv4 dotted format).
     *
     * @param mixed $val The value to convert to string.
     */
    public function stringVal($val): string
    {
        if (is_string($val)) {
            return $val;
        } else {
            return long2ip($val);
        }
    }

    /**
     * Get the IP value in the suitable format for storage.
     *
     * @param mixed $val The value to convert to string.
     * @return string
     *@see StorablePropertyTrait::storageVal()
     */
    #[\Override]
    public function storageVal(mixed $val): int|false|string
    {
        $mode = $this->getStorageMode();

        if ($mode === self::STORAGE_MODE_INT) {
            return $this->intVal($val);
        } else {
            return $this->stringVal($val);
        }
    }

    /**
     * Get the hostname currently associated with an IP value.
     *
     * @param mixed $val The value to convert to string.
     * @return string
     */
    public function hostname($val): string|false
    {
        $val = $this->stringVal($val);
        return gethostbyaddr($val);
    }

    /**
     * @see StorableProperyTrait:sqlType()
     */
    public function sqlType(): string
    {
        $mode = $this->getStorageMode();

        if ($mode === self::STORAGE_MODE_INT) {
            return 'BIGINT';
        } else {
            return 'VARCHAR(15)';
        }
    }

    /**
     * @see StorableProperyTrait::sqlPdoType()
     */
    public function sqlPdoType(): int
    {
        $mode = $this->getStorageMode();

        if ($mode === self::STORAGE_MODE_INT) {
            return PDO::PARAM_INT;
        } else {
            return PDO::PARAM_STR;
        }
    }
}
