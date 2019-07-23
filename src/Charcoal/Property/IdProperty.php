<?php

namespace Charcoal\Property;

use PDO;
use DomainException;
use InvalidArgumentException;

// From 'charcoal-property'
use Charcoal\Property\AbstractProperty;

/**
 * ID Property
 */
class IdProperty extends AbstractProperty
{
    const MODE_AUTO_INCREMENT = 'auto-increment';
    const MODE_CUSTOM = 'custom';
    const MODE_UNIQID = 'uniqid';
    const MODE_UUID = 'uuid';

    const DEFAULT_MODE = self::MODE_AUTO_INCREMENT;

    /**
     * The ID mode.
     *
     * One of:
     * - `auto-increment` (default). Database auto-increment.
     * - `custom`. A user supplied unique identifier.
     * - `uniq`. Generated with php's `uniqid()`.
     * - `uuid`. A (randomly-generated) universally unique identifier (RFC-4122 v4) .
     *
     * @var string $mode
     */
    private $mode = self::DEFAULT_MODE;

    /**
     * Retrieve the property type.
     *
     * @return string
     */
    public function type()
    {
        return 'id';
    }

    /**
     * Ensure multiple can not be TRUE for ID property (ID must be unique per object).
     *
     * @see    AbstractProperty::setMultiple()
     * @param  boolean $flag The multiple flag.
     * @throws InvalidArgumentException If the multiple argument is TRUE (must be FALSE).
     * @see AbstractProperty::setMultiple()
     * @return self
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
     * Ensure l10n can not be TRUE for ID property (ID must be unique per object).
     *
     * @see    AbstractProperty::setL10n()
     * @param  boolean $flag The l10n, or "translatable" flag.
     * @throws InvalidArgumentException If the L10N argument is TRUE (must be FALSE).
     * @see AbstractProperty::setL10n()
     * @return self
     */
    public function setL10n($flag)
    {
        $flag = !!$flag;

        if ($flag === true) {
            throw new InvalidArgumentException(
                'The ID property can not be translatable.'
            );
        }

        return $this;
    }

    /**
     * L10N is always FALSE for ID property.
     *
     * @see    AbstractProperty::getL10n()
     * @return boolean
     */
    public function getL10n()
    {
        return false;
    }

    /**
     * Retrieve the available ID modes.
     *
     * @return array
     */
    public function availableModes()
    {
        return [
            self::MODE_AUTO_INCREMENT,
            self::MODE_CUSTOM,
            self::MODE_UNIQID,
            self::MODE_UUID
        ];
    }

    /**
     * Set the allowed ID mode.
     *
     * @param string $mode The ID mode ("auto-increment", "custom", "uniqid" or "uuid").
     * @throws InvalidArgumentException If the mode is not one of the 4 valid modes.
     * @return self
     */
    public function setMode($mode)
    {
        $availableModes = $this->availableModes();
        if (!in_array($mode, $availableModes)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid ID mode. Must be one of "%s"',
                implode(', ', $availableModes)
            ));
        }

        $this->mode = $mode;

        return $this;
    }

    /**
     * Retrieve the allowed ID mode.
     *
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Prepare the value for save.
     *
     * If no ID is set upon first save, then auto-generate it if necessary.
     *
     * @param mixed $val The value, at time of saving.
     * @return mixed
     */
    public function save($val)
    {
        if (!$val) {
            $val = $this->autoGenerate();
        }

        return $val;
    }

    /**
     * Auto-generate a value upon first save.
     *
     * - self::MODE_AUTOINCREMENT
     *   - null: The auto-generated value should be handled at the database driver level.
     * - self::MODE_CUSTOM
     *   - null: Custom mode must be defined elsewhere.
     * - self::MODE_UNIQID
     *   - A random 13-char `uniqid()` value.
     * - self::MODE_UUID
     *   - A random RFC-4122 UUID value.
     *
     * @throws DomainException If the mode does not have a value generator.
     * @return string|null
     */
    public function autoGenerate()
    {
        $mode = $this['mode'];

        if ($mode === self::MODE_UNIQID) {
            return uniqid();
        } elseif ($mode === self::MODE_UUID) {
            return $this->generateUuid();
        }

        return null;
    }

    /**
     * Generate a RFC-4122 v4 Universally-Unique Identifier (UUID).
     *
     * @see http://tools.ietf.org/html/rfc4122#section-4.4
     * @return string
     */
    private function generateUuid()
    {
        // Generate a uniq string identifer (valid v4 uuid)
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low" flag
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            // 16 bits for "time_mid" flag
            mt_rand(0, 0xffff),
            // 16 bits for "time_hi_andVersion" flat (4 most significant bits holds version number)
            (mt_rand(0, 0x0fff) | 0x4000),
            // 16 bits, 8 bits for "clk_seq_hi_res" flag and 8 bits for "clk_seq_low" flag
            // two most significant bits holds zero and one for variant DCE1.1
            (mt_rand(0, 0x3fff) | 0x8000),
            // 48 bits for "node" flag
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }

    /**
     * Get additional SQL field options.
     *
     *
     * @see StorablePropertyTrait::sqlExtra()
     * @return string
     */
    public function sqlExtra()
    {
        $mode = $this->getMode();

        if ($mode === self::MODE_AUTO_INCREMENT) {
            return 'AUTO_INCREMENT';
        } else {
            return '';
        }
    }

    /**
     * Get the SQL data type (Storage format).
     *
     * - For "auto-increment" ids, it is an integer.
     * - For "custom" ids, it is a 255-varchar string.
     * - For "uniqid" ids, it is a 13-char string.
     * - For "uuid" id, it is a 36-char string.
     *
     * @see StorablePropertyTrait::sqlType()
     * @return string The SQL type.
     */
    public function sqlType()
    {
        $mode = $this->getMode();

        if ($mode === self::MODE_AUTO_INCREMENT) {
            $dbDriver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
            if ($dbDriver === 'sqlite') {
                return 'INT';
            } else {
                return 'INT(10) UNSIGNED';
            }
        } elseif ($mode === self::MODE_UNIQID) {
            return 'CHAR(13)';
        } elseif ($mode === self::MODE_UUID) {
            return 'CHAR(36)';
        } elseif ($mode === self::MODE_CUSTOM) {
            return 'VARCHAR(255)';
        }
    }

    /**
     * Get the PDO data type.
     *
     * @see StorablePropertyTrait::sqlPdoType()
     * @return integer
     */
    public function sqlPdoType()
    {
        $mode = $this->getMode();

        if ($mode === self::MODE_AUTO_INCREMENT) {
            return PDO::PARAM_INT;
        } else {
            return PDO::PARAM_STR;
        }
    }
}
