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
    const STORAGE_MODE_STRING = 'string';
    const STORAGE_MODE_INT = 'int';

    const DEFAULT_STORAGE_MODE = self::STORAGE_MODE_STRING;

    /**
     * The storage mode can be either "string" (default) or "int".
     *
     * @var string $storageMode
     */
    private $storageMode = self::DEFAULT_STORAGE_MODE;

    /**
     * Retrieve the property type.
     *
     * @return string
     */
    public function type()
    {
        return 'ip';
    }

    /**
     * Ensure multiple can not be TRUE for ID property.
     *
     * @param  boolean $flag The multiple flag.
     * @throws InvalidArgumentException If the multiple argument is TRUE (must be FALSE).
     * @see    AbstractProperty::setMultiple()
     * @return IdProperty Chainable
     */
    public function setMultiple($flag)
    {
        $flag = !!$flag;

        if ($flag === true) {
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
     * @return boolean
     */
    public function getMultiple()
    {
        return false;
    }

    /**
     * Ensure l10n can not be TRUE for IP property.
     *
     * @param  boolean $flag The l10n, or "translatable" flag.
     * @throws InvalidArgumentException If the L10N argument is TRUE (must be FALSE).
     * @see    AbstractProperty::setL10n()
     * @return IdProperty Chainable
     */
    public function setL10n($flag)
    {
        $flag = !!$flag;

        if ($flag === true) {
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
     * @return boolean
     */
    public function getL10n()
    {
        return false;
    }

    /**
     * @param string $mode Either "string" or "int".
     * @throws InvalidArgumentException If the storage mode is invalid.
     * @return self
     */
    public function setStorageMode($mode)
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

    /**
     * @return string
     */
    public function getStorageMode()
    {
        return $this->storageMode;
    }

    /**
     * Get the IP value as a long integer.
     *
     * @param mixed $val The value to convert (if necessary) to integer.
     * @return integer
     */
    public function intVal($val)
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
     * @return string
     */
    public function stringVal($val)
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
     * @see StorablePropertyTrait::storageVal()
     * @return string
     */
    public function storageVal($val)
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
    public function hostname($val)
    {
        $val = $this->stringVal($val);
        return gethostbyaddr($val);
    }


    /**
     * @see StorableProperyTrait:sqlType()
     * @return string
     */
    public function sqlType()
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
     * @return integer
     */
    public function sqlPdoType()
    {
        $mode = $this->getStorageMode();

        if ($mode === self::STORAGE_MODE_INT) {
            return PDO::PARAM_INT;
        } else {
            return PDO::PARAM_STR;
        }
    }
}
