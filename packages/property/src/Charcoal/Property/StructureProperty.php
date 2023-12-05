<?php

namespace Charcoal\Property;

use PDO;
use InvalidArgumentException;
use UnexpectedValueException;
// From 'charcoal-translator'
use Charcoal\Translator\TranslatableInterface;
use Charcoal\Translator\TranslatableValue;
use Charcoal\Translator\Translation;
// From 'charcoal-property'
use Charcoal\Property\AbstractProperty;

/**
 * Structure Data Property
 *
 * Allows for multiple complex entries to a property, which are stored
 * as a JSON string in the model's storage source. Typical use cases would be
 * {@see \Charcoal\Property\MapStructureProperty geolocation coordinates},
 * grouped properties, details for a log, or a list of addresses or people.
 *
 * The property's value is free to be anything serializable.
 * To constrain the property's value or to exert greater control
 * over the structure's data types, consider using {@see ModelStructureProperty}.
 */
class StructureProperty extends AbstractProperty
{
    /**
     * The SQL data type.
     *
     * @var string
     */
    private string $sqlType = 'TEXT';

    /**
     * Retrieve the property's type identifier.
     *
     * @return string
     */
    public function type()
    {
        return 'structure';
    }

    /**
     * @param  mixed $val     The value to convert for display.
     * @param  array $options Optional display options.
     * @return string
     */
    public function displayVal($val, array $options = [])
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
        } elseif ($val instanceof TranslatableValue) {
            $propertyValue = $val->trans($this->translator());
        } elseif ($val instanceof Translation) {
            $propertyValue = (string)$val;
        } else {
            $propertyValue = $val;
        }

        if (!is_scalar($propertyValue)) {
            $flags = ($options['json'] ?? JSON_PRETTY_PRINT);
            return json_encode($propertyValue, $flags);
        }

        return (string)$propertyValue;
    }

    /**
     * Get the property's value in a format suitable for storage.
     *
     * @param  mixed $val Optional. The value to convert to storage value.
     * @return mixed
     */
    public function storageVal($val)
    {
        if ($val === null || $val === '') {
            // Do not serialize NULL values
            return null;
        }

        if ($val instanceof TranslatableValue) {
            $val = $val->trans($this->translator());
        }

        if ($val instanceof Translation) {
            $val = (string)$val;
        }

        if (!is_scalar($val)) {
            return json_encode($val, JSON_UNESCAPED_UNICODE);
        }

        return $val;
    }

    /**
     * Attempt to get the multilingual value in the requested language.
     *
     * @param  mixed $val  The multilingual value to lookup.
     * @param  mixed $lang The language to return the value in.
     * @return string|null
     */
    protected function l10nVal($val, $lang = null): ?string
    {
        if (!is_string($lang)) {
            if (is_array($lang) && isset($lang['lang'])) {
                $lang = $lang['lang'];
            } else {
                $lang = $this->translator()->getLocale();
            }
        }

        if ($val instanceof TranslatableValue) {
            return $val->trans($this->translator(), $lang);
        }

        return ($val[$lang] ?? null);
    }

    /**
     * Retrieve the structure as a plain array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->val();
    }

    /**
     * Retrieve the structure of items as JSON.
     *
     * @param  integer $options Bitmask of flags.
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->val(), $options);
    }

    /**
     * AbstractProperty > setVal(). Ensure val is an array
     *
     * @param  string|array $val The value to set.
     * @throws InvalidArgumentException If the value is invalid.
     * @return array
     */
    public function parseOne($val)
    {
        if ($val === null || $val === '') {
            if ($this['allowNull']) {
                return null;
            } else {
                throw new InvalidArgumentException(
                    'Value can not be NULL (not allowed)'
                );
            }
        }

        if (!is_array($val)) {
            $val = json_decode($val, true);
        }

        return $val;
    }

    /**
     * Creates a custom data value object with a similar API to the
     * {@see \Charcoal\Translator\Translation Translation class}.
     *
     * @param mixed $val A L10N variable.
     * @return TranslatableInterface|null The translation value.
     */
    public function parseValAsL10n($val): ?TranslatableInterface
    {
        return new TranslatableValue($val);
    }

    /**
     * Set the property's SQL encoding & collation.
     *
     * @param string $sqlType The field SQL column type.
     * @throws InvalidArgumentException If the SQL type is invalid.
     * @return self
     */
    public function setSqlType($sqlType)
    {
        if (!is_string($sqlType)) {
            throw new InvalidArgumentException(
                'SQL Type must be a string.'
            );
        }

        switch (strtoupper($sqlType)) {
            case 'TEXT':
                $sqlType = 'TEXT';
                break;

            case 'TINY':
            case 'TINYTEXT':
                $sqlType = 'TINYTEXT';
                break;

            case 'MEDIUM':
            case 'MEDIUMTEXT':
                $sqlType = 'MEDIUMTEXT';
                break;

            case 'LONG':
            case 'LONGTEXT':
                $sqlType = 'LONGTEXT';
                break;

            default:
                throw new InvalidArgumentException(
                    'SQL Type must be one of TEXT, TINYTEXT, MEDIUMTEXT, LONGTEXT.'
                );
        }

        $this->sqlType = $sqlType;

        return $this;
    }

    /**
     * Retrieve the property's SQL data type (storage format).
     *
     * For a lack of better array support in mysql, data is stored as encoded JSON in a TEXT.
     *
     * @see StorableProperyTrait::sqlType()
     * @return string
     */
    public function sqlType(): string
    {
        return $this->sqlType;
    }

    /**
     * Retrieve the property's PDO data type.
     *
     * @see StorablePropertyTrait::sqlPdoType()
     * @return integer
     */
    public function sqlPdoType()
    {
        return PDO::PARAM_STR;
    }
}
