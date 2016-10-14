<?php

namespace Charcoal\Property;

use \Charcoal\Translation\TranslationConfig;
use \Charcoal\Translation\TranslationString;

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
     * @return PropertyField[]
     */
    public function fields()
    {
        if ($this->fields === null) {
            $this->generateFields();
        } else {
            $this->updatedFields();
        }

        return $this->fields;
    }

    /**
     * Update the property's storage fields.
     *
     * @return PropertyField[]
     */
    protected function updatedFields()
    {
        if ($this->fields === null) {
            $this->generateFields();
        }

        if ($this->l10n()) {
            $translator = TranslationConfig::instance();

            foreach ($translator->availableLanguages() as $langCode) {
                $this->fields[$langCode]->setVal($this->fieldVal($langCode));
            }
        } else {
            $this->fields[0]->setVal($this->storageVal($this->val()));
        }

        return $this->fields;
    }

    /**
     * Reset the property's storage fields.
     *
     * @return PropertyField[]
     */
    protected function generateFields()
    {
        $this->fields = [];
        if ($this->l10n()) {
            $translator = TranslationConfig::instance();

            foreach ($translator->availableLanguages() as $langCode) {
                $ident = sprintf('%1$s_%2$s', $this->ident(), $langCode);
                $field = new PropertyField();
                $field->setData([
                    'ident'      => $ident,
                    'sqlType'    => $this->sqlType(),
                    'sqlPdoType' => $this->sqlPdoType(),
                    'extra'      => $this->sqlExtra(),
                    'val'        => $this->fieldVal($langCode),
                    'defaultVal' => null,
                    'allowNull'  => $this->allowNull()
                ]);
                $this->fields[$langCode] = $field;
            }
        } else {
            $val = $this->val();
            $field = new PropertyField();
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
     * @return mixed
     */
    protected function fieldVal($fieldIdent)
    {
        $val = $this->val();

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

        if (!$this->l10n() && $val instanceof TranslationString) {
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
}
