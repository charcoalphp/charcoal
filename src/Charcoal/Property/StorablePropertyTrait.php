<?php

namespace Charcoal\Property;

use \Charcoal\Translator\Translation;

use \Charcoal\Property\PropertyField;

/**
 *
 */
trait StorablePropertyTrait
{

    /**
     * Store of the property's storage fields.
     *
     * @var PropertyField[]
     */
    private $fields;

    /**
     * Retrieve the property's storage fields.
     *
     * @param  mixed $val The value to set as field value.
     * @return PropertyField[]
     */
    public function fields($val)
    {
        if ($this->fields === null) {
            $this->generateFields($val);
        } else {
            $this->updatedFields($val);
        }

        return $this->fields;
    }

    /**
     * Update the property's storage fields.
     *
     * @param  mixed $val The value to set as field value.
     * @return PropertyField[]
     */
    private function updatedFields($val)
    {
        if ($this->fields === null) {
            $this->generateFields($val);
        }

        if ($this->l10n()) {
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
        if ($this->l10n()) {
            foreach ($this->translator()->availableLocales() as $langCode) {
                $ident = sprintf('%1$s_%2$s', $this->ident(), $langCode);
                $field = new PropertyField([
                    'translator' => $this->translator()
                ]);
                $field->setData([
                    'ident'      => $ident,
                    'sqlType'    => $this->sqlType(),
                    'sqlPdoType' => $this->sqlPdoType(),
                    'extra'      => $this->sqlExtra(),
                    'val'        => $this->fieldVal($langCode, $val),
                    'defaultVal' => null,
                    'allowNull'  => $this->allowNull()
                ]);
                $this->fields[$langCode] = $field;
            }
        } else {
            $field = new PropertyField([
                'translator' => $this->translator()
            ]);
            $field->setData([
                'ident'      => $this->ident(),
                'sqlType'    => $this->sqlType(),
                'sqlPdoType' => $this->sqlPdoType(),
                'extra'      => $this->sqlExtra(),
                'val'        => $this->storageVal($val),
                'defaultVal' => null,
                'allowNull'  => $this->allowNull()
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

        if (!$this->l10n() && $val instanceof Translation) {
            $val = (string)$val;
        }

        if ($this->multiple()) {
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
    abstract public function sqlExtra();

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
    abstract public function ident();

    /**
     * @return boolean
     */
    abstract public function l10n();

    /**
     * @return boolean
     */
    abstract public function multiple();

    /**
     * @return boolean
     */
    abstract public function allowNull();

    /**
     * @return \Charcoal\Translator\Translator
     */
    abstract protected function translator();
}
