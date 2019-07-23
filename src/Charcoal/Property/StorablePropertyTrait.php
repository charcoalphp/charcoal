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
     * Store of the property's storage fields.
     *
     * @var PropertyField[]|null
     */
    private $fields;

    /**
     * An empty value implies that the property will inherit the table's encoding
     * @var string
     */
    private $sqlEncoding = '';

    /**
     * Set the property's SQL encoding & collation.
     *
     * @param  string $ident The encoding ident.
     * @throws InvalidArgumentException  If the identifier is not a string.
     * @return self
     */
    public function setSqlEncoding($ident)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(
                'Encoding ident needs to be string.'
            );
        }

        if ($ident === 'utf8mb4') {
            $this->sqlEncoding = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
        }

        return $this;
    }

    /**
     * Retrieve the property's SQL encoding ident.
     *
     * @return string
     */
    public function sqlEncoding()
    {
        return $this->sqlEncoding;
    }

    /**
     * Retrieve the property's storage fields.
     *
     * @param  mixed $val The value to set as field value.
     * @return PropertyField[]
     */
    public function fields($val)
    {
        if (empty($this->fields)) {
            $this->generateFields($val);
        } else {
            $this->updatedFields($val);
        }

        return $this->fields;
    }

    /**
     * Retrieve the property's storage field names.
     *
     * @return string[]
     */
    public function fieldNames()
    {
        $fields = [];
        if ($this['l10n']) {
            foreach ($this->translator()->availableLocales() as $langCode) {
                $fields[$langCode] = $this->l10nIdent($langCode);
            }
        } else {
            $fields[] = $this->ident();
        }

        return $fields;
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
            // Do not json_encode NULL values
            return null;
        }

        if (!$this['l10n'] && $val instanceof Translation) {
            $val = (string)$val;
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
     * @return string
     */
    public function sqlExtra()
    {
        return '';
    }

    /**
     * @return string
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
     * @param  string|null $lang The language code to return the identifier with.
     * @return string
     */
    abstract public function l10nIdent($lang = null);

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
     * @param  array $data Optional. Field data.
     * @return PropertyField
     */
    protected function createPropertyField(array $data = null)
    {
        $field = new PropertyField([
            'translator' => $this->translator()
        ]);

        if ($data !== null) {
            $field->setData($data);
        }

        return $field;
    }

    /**
     * @return \Charcoal\Translator\Translator
     */
    abstract protected function translator();

    /**
     * Update the property's storage fields.
     *
     * @param  mixed $val The value to set as field value.
     * @return PropertyField[]
     */
    private function updatedFields($val)
    {
        if (empty($this->fields)) {
            return $this->generateFields($val);
        }

        if ($this['l10n']) {
            foreach ($this->translator()->availableLocales() as $langCode) {
                $this->fields[$langCode]->setVal($this->fieldVal($langCode, $val));
            }
        } else {
            $this->fields[0]->setVal($this->storageVal($val));
        }

        return $this->fields;
    }

    /**
     * Reset the property's storage fields.
     *
     * @param  mixed $val The value to set as field value.
     * @return PropertyField[]
     */
    private function generateFields($val)
    {
        $this->fields = [];
        if ($this['l10n']) {
            foreach ($this->translator()->availableLocales() as $langCode) {
                $ident = $this->l10nIdent($langCode);
                $field = $this->createPropertyField([
                    'ident'       => $ident,
                    'sqlType'     => $this->sqlType(),
                    'sqlPdoType'  => $this->sqlPdoType(),
                    'sqlEncoding' => $this->sqlEncoding(),
                    'extra'       => $this->sqlExtra(),
                    'val'         => $this->fieldVal($langCode, $val),
                    'defaultVal'  => null,
                    'allowNull'   => $this['allowNull']
                ]);
                $this->fields[$langCode] = $field;
            }
        } else {
            $field = $this->createPropertyField([
                'ident'       => $this->ident(),
                'sqlType'     => $this->sqlType(),
                'sqlPdoType'  => $this->sqlPdoType(),
                'sqlEncoding' => $this->sqlEncoding(),
                'extra'       => $this->sqlExtra(),
                'val'         => $this->storageVal($val),
                'defaultVal'  => null,
                'allowNull'   => $this['allowNull']
            ]);
            $this->fields[] = $field;
        }

        return $this->fields;
    }

    /**
     * Retrieve the value of the property's given storage field.
     *
     * @param  string $fieldIdent The property field identifier.
     * @param  mixed  $val        The value to set as field value.
     * @return mixed
     */
    private function fieldVal($fieldIdent, $val)
    {
        if ($val === null) {
            return null;
        }

        if (is_scalar($val)) {
            return $this->storageVal($val);
        }

        if (isset($val[$fieldIdent])) {
            return $this->storageVal($val[$fieldIdent]);
        } else {
            return null;
        }
    }
}
