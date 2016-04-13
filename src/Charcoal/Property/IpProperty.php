<?php

namespace Charcoal\Property;

// Dependencies from `PHP`
use \InvalidArgumentException;

// Dependencies from `PHP` extensions
use \PDO;


// Module `charcoal-core` dependencies
use \Charcoal\Property\AbstractProperty;

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
     * @see    AbstractProperty::setMultiple()
     * @param  boolean $flag The multiple flag.
     * @throws InvalidArgumentException If the multiple argument is TRUE (must be FALSE).
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
     * @see    AbstractProperty::multiple()
     * @return boolean
     */
    public function multiple()
    {
        return false;
    }

    /**
     * Ensure l10n can not be TRUE for IP property.
     *
     * @see    AbstractProperty::setL10n()
     * @param  boolean $flag The l10n, or "translatable" flag.
     * @throws InvalidArgumentException If the L10N argument is TRUE (must be FALSE).
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
     * @see    AbstractProperty::l10n()
     * @return boolean
     */
    public function l10n()
    {
        return false;
    }

    /**
     * @param string $mode Either "string" or "int".
     * @throws InvalidArgumentException If the storage mode is invalid.
     * @return IpProperty Chainable
     */
    public function setStorageMode($mode)
    {
        $validModes = [
            self::STORAGE_MODE_STRING,
            self::STORAGE_MODE_INT
        ];
        if (!in_array($mode, $validModes)) {
            throw new InvalidArgumentException(
                'Can not set storage mode: invalid mode.'
            );
        }
        $this->storageMode = $mode;
        return $this;
    }

    /**
     * @return string
     */
    public function storageMode()
    {
        return $this->storageMode;
    }

    /**
     * Prepare the value for save.
     *
     * If no ID is set upon first save, then auto-generate it if necessary.
     *
     * @see Charcoal_Object::save()
     * @return mixed
     */
    public function save()
    {
        return $this->val();
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
     * @return string
     */
    public function storageVal($val = null)
    {
        if ($val === null) {
            $val = $this->val();
        }

        $mode = $this->storageMode();

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
     * @return string
     */
    public function sqlExtra()
    {
        return '';
    }
    /**
     * @return string
     */
    public function sqlType()
    {
        $mode = $this->storageMode();

        if ($mode === self::STORAGE_MODE_INT) {
            return 'BIGINT';
        } else {
            return 'VARCHAR(15)';
        }
    }

    /**
     * @return integer
     */
    public function sqlPdoType()
    {
        $mode = $this->storageMode();

        if ($mode === self::STORAGE_MODE_INT) {
            return PDO::PARAM_INT;
        } else {
            return PDO::PARAM_STR;
        }
    }
}
