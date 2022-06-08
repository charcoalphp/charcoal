<?php

namespace Charcoal\Property;

use InvalidArgumentException;

// From 'charcoal-translator'
use Charcoal\Translator\Translation;

// From 'charcoal-property'
use Charcoal\Property\PropertyField;

/**
 *
 */
trait StorablePropertyTrait
{
    /**
     * An empty value implies that the property will inherit the table's encoding.
     *
     * @var string|null
     */
    private $sqlEncoding;

    /**
     * The property's identifier formatted for field names.
     *
     * @var string
     */
    private $fieldIdent;

    /**
     * Store of the property's storage fields.
     *
     * @var PropertyField[]
     */
    protected $fields;

    /**
     * Store of the property's storage field identifiers.
     *
     * @var string[]
     */
    protected $fieldNames;

    /**
     * Holds a list of all snake_case strings.
     *
     * @var string[]
     */
    protected static $snakeCache = [];

    /**
     * Retrieve the property's storage fields.
     *
     * @param  mixed $val The value to set as field value.
     * @return PropertyField[]
     */
    public function fields($val = null)
    {
        if (empty($this->fields)) {
            $this->fields = $this->generateFields($val);
        } else {
            $this->fields = $this->updatedFields($this->fields, $val);
        }

        return $this->fields;
    }

    /**
     * Retrieve the property's identifier formatted for field names.
     *
     * @param  string|null $key The field key to suffix to the property identifier.
     * @return string|null Returns the property's field name.
     *     If $key is provided, returns the namespaced field name otherwise NULL.
     */
    public function fieldIdent($key = null)
    {
        if ($this->fieldIdent === null) {
            $this->fieldIdent = $this->snakeize($this['ident']);
        }

        if ($key === null || $key === '') {
            return $this->fieldIdent;
        }

        if ($this->isValidFieldKey($key)) {
            return $this->fieldIdent.'_'.$this->snakeize($key);
        }

        return null;
    }

    /**
     * Retrieve the property's namespaced storage field names.
     *
     * Examples:
     * 1. `name`: `name_en`, `name_fr`, `name_de`
     * 2. `obj`: `obj_id`, `obj_type`
     * 3. `file`: `file`, `file_type`
     * 4. `opt`: `opt_0`, `opt_1`, `opt_2`
     *
     * @return string[]
     */
    public function fieldNames()
    {
        if ($this->fieldNames === null) {
            $names = [];
            if ($this['l10n']) {
                $keys = $this->translator()->availableLocales();
                foreach ($keys as $key) {
                    $names[$key] = $this->fieldIdent($key);
                }
            } else {
                $names[''] = $this->fieldIdent();
            }

            $this->fieldNames = $names;
        }

        return $this->fieldNames;
    }

    /**
     * Retrieve the property's value in a format suitable for the given field key.
     *
     * @param  string $key The property field key.
     * @param  mixed  $val The value to set as field value.
     * @return mixed
     */
    protected function fieldValue($key, $val)
    {
        if ($val === null) {
            return null;
        }

        if (is_scalar($val)) {
            return $this->storageVal($val);
        }

        if (!$this->isValidFieldKey($key)) {
            return $this->storageVal($val);
        }

        if (isset($val[$key])) {
            return $this->storageVal($val[$key]);
        }

        return null;
    }

    /**
     * Retrieve the property's value in a format suitable for storage.
     *
     * @param  mixed $val The value to convert for storage.
     * @return mixed
     */
    public function storageVal($val)
    {
        if ($val === null) {
            // Do not serialize NULL values
            return null;
        }

        if ($val instanceof Translation) {
            // Do not serialize Translation objects
            $val = (string)$val;
        }

        if ($this['l10n']) {
            if ($val === '') {
                return $val;
            }
        } else {
            if ($this['allowNull'] && $val === '') {
                return null;
            }
        }

        if ($this['multiple']) {
            if (is_array($val)) {
                $val = implode($this->multipleSeparator(), $val);
            }
        }

        if (!is_scalar($val)) {
            return json_encode($val, JSON_UNESCAPED_UNICODE);
        }

        return $val;
    }

    /**
     * Parse the property's value (from a flattened structure)
     * in a format suitable for models.
     *
     * This method takes a one-dimensional array and, depending on
     * the property's {@see self::fieldNames() field structure},
     * returns a complex array.
     *
     * @param  array $flatData The model data subset.
     * @return mixed
     */
    public function parseFromFlatData(array $flatData)
    {
        $value = null;

        $fieldNames = $this->fieldNames();
        foreach ($fieldNames as $fieldKey => $fieldName) {
            if ($this->isValidFieldKey($fieldKey)) {
                if (isset($flatData[$fieldName])) {
                    $value[$fieldKey] = $flatData[$fieldName];
                } elseif ($this['l10n']) {
                    $value[$fieldKey] = '';
                }
            } elseif (isset($flatData[$fieldName])) {
                $value = $flatData[$fieldName];
            }
        }

        return $value;
    }

    /**
     * Update the property's storage fields.
     *
     * @param  PropertyField[] $fields The storage fields to update.
     * @param  mixed           $val    The value to set as field value.
     * @return PropertyField[]
     */
    protected function updatedFields(array $fields, $val)
    {
        if (empty($fields)) {
            $fields = $this->generateFields($val);
        }

        foreach ($fields as $fieldKey => $field) {
            $fields[$fieldKey]->setVal($this->fieldValue($fieldKey, $val));
        }

        return $fields;
    }

    /**
     * Reset the property's storage fields.
     *
     * @param  mixed $val The value to set as field value.
     * @return PropertyField[]
     */
    protected function generateFields($val = null)
    {
        $fields = [];

        $fieldNames = $this->fieldNames();
        foreach ($fieldNames as $fieldKey => $fieldName) {
            $field = $this->createPropertyField([
                'ident'       => $fieldName,
                'sqlType'     => $this->sqlType(),
                'sqlPdoType'  => $this->sqlPdoType(),
                'sqlEncoding' => $this->sqlEncoding(),
                'extra'       => $this->sqlExtra(),
                'val'         => $this->fieldValue($fieldKey, $val),
                'defaultVal'  => $this->sqlDefaultVal(),
                'allowNull'   => $this['allowNull'],
            ]);

            $fields[$fieldKey] = $field;
        }

        return $fields;
    }

    /**
     * @param  array $data Optional. Field data.
     * @return PropertyField
     */
    protected function createPropertyField(array $data = null)
    {
        $field = new PropertyField();

        if ($data !== null) {
            $field->setData($data);
        }

        return $field;
    }

    /**
     * Determine if the given value is a valid field key suffix.
     *
     * @param  mixed $key The key to test.
     * @return boolean
     */
    protected function isValidFieldKey($key)
    {
        return (!empty($key) || is_numeric($key));
    }

    /**
     * Transform a string from "camelCase" to "snake_case".
     *
     * @param  string $value The string to snakeize.
     * @return string The snake_case string.
     */
    protected function snakeize($value)
    {
        $key = $value;

        if (isset(static::$snakeCache[$key])) {
            return static::$snakeCache[$key];
        }

        $value = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $value));

        static::$snakeCache[$key] = $value;

        return static::$snakeCache[$key];
    }

    /**
     * Set the property's SQL encoding & collation.
     *
     * @param  string|null $encoding The encoding identifier or SQL encoding and collation.
     * @throws InvalidArgumentException  If the identifier is not a string.
     * @return self
     */
    public function setSqlEncoding($encoding)
    {
        if (!is_string($encoding) && $encoding !== null) {
            throw new InvalidArgumentException(
                'Encoding ident needs to be string.'
            );
        }

        if ($encoding === 'utf8mb4') {
            $encoding = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
        }

        $this->sqlEncoding = $encoding;
        return $this;
    }

    /**
     * Retrieve the property's SQL encoding & collation.
     *
     * @return string|null
     */
    public function sqlEncoding()
    {
        return $this->sqlEncoding;
    }

    /**
     * @return string|null
     */
    public function sqlExtra()
    {
        return null;
    }

    /**
     * @return string|null
     */
    public function sqlDefaultVal()
    {
        return null;
    }

    /**
     * @return string|null
     */
    abstract public function sqlType();

    /**
     * @return integer
     */
    abstract public function sqlPdoType();

    /**
     * @return string
     */
    abstract public function getIdent();

    /**
     * @return boolean
     */
    abstract public function getL10n();

    /**
     * @return boolean
     */
    abstract public function getMultiple();

    /**
     * @return string
     */
    abstract public function multipleSeparator();

    /**
     * @return boolean
     */
    abstract public function getAllowNull();

    /**
     * @return \Charcoal\Translator\Translator
     */
    abstract protected function translator();
}
