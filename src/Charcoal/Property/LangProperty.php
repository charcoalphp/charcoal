<?php

namespace Charcoal\Property;

use PDO;

// From 'charcoal-translator'
use Charcoal\Translator\Translation;

// From 'charcoal-property'
use Charcoal\Property\AbstractProperty;
use Charcoal\Property\SelectablePropertyInterface;
use Charcoal\Property\SelectablePropertyTrait;

/**
 * Language property
 *
 * Provides an immutable list of selectable locales based on the available languages of your application.
 */
class LangProperty extends AbstractProperty implements SelectablePropertyInterface
{
    use SelectablePropertyTrait;

    /**
     * @return string
     */
    public function type()
    {
        return 'lang';
    }

    /**
     * Ensure the choices are never explicitly set, as they will always be auto-generated from environment / config.
     *
     * @param  array $choices One or more choice structures.
     * @see SelectablePropertyTrait::setChoices()
     * @return self
     */
    public function setChoices(array $choices)
    {
        unset($choices);

        $this->logger->debug(
            'Choices can not be set for language properties. They are auto-generated from available languages.'
        );

        return $this;
    }

    /**
     * Ensure the choices are never explicitly set, as they will always be auto-generated from environment / config.
     *
     * @param  array $choices One or more choice structures.
     * @see SelectablePropertyTrait::setChoices()
     * @return self
     */
    public function addChoices(array $choices)
    {
        unset($choices);

        $this->logger->debug(
            'Choices can not be added for language properties. They are auto-generated from available languages.'
        );

        return $this;
    }

    /**
     * Ensure the choices are never explicitly set, as they will always be auto-generated from environment / config.
     *
     * @param string       $choiceIdent The choice identifier (will be key / default ident).
     * @param string|array $choice      A string representing the choice label or a structure.
     * @see SelectablePropertyTrait::addChoice()
     * @return LangProperty Chainable.
     */
    public function addChoice($choiceIdent, $choice)
    {
        unset($choiceIdent, $choice);

        $this->logger->debug(
            'Choices can not be added for language properties. They are auto-generated from available languages.'
        );

        return $this;
    }

    /**
     * Determine if choices are available.
     *
     * @see SelectablePropertyTrait::hasChoices()
     * @return boolean
     */
    public function hasChoices()
    {
        return !!$this->translator()->locales();
    }

    /**
     * Determine if the given choice is available.
     *
     * @param  string $choiceIdent The choice identifier to lookup.
     * @see SelectablePropertyTrait::hasChoice()
     * @return boolean
     */
    public function hasChoice($choiceIdent)
    {
        if (empty($this->choices)) {
            $this->choices();
        }

        return isset($this->choices[$choiceIdent]);
    }

    /**
     * Retrieve the available choice structures.
     *
     * @see    SelectablePropertyTrait::choices()
     * @return array
     */
    public function choices()
    {
        if (empty($this->choices)) {
            $locales = $this->translator()->locales();
            if ($locales) {
                $selected = (array)$this->val();
                $choices  = [];

                foreach ($locales as $langCode => $localeStruct) {
                    /**
                     * @see \Charcoal\Admin\Widget\FormSidebarWidget::languages()
                     * @see \Charcoal\Admin\Widget\FormGroupWidget::languages()
                     */
                    if (isset($localeStruct['name'])) {
                        $label = $this->translator()->translation($localeStruct['name']);
                    } else {
                        $trans = 'locale.'.$langCode;
                        if ($trans === $this->translator()->translate($trans)) {
                            $label = strtoupper($langCode);
                        } else {
                            $label = $this->translator()->translation($trans);
                        }
                    }

                    $choices[$langCode] = [
                        'label'    => $label,
                        'selected' => in_array($langCode, $selected),
                        'value'    => $langCode
                    ];
                }

                $this->choices = $choices;
            }
        }

        return $this->choices;
    }

    /**
     * Format the given value for display.
     *
     * @param  mixed $val     The value to to convert for display.
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
        } elseif ($val instanceof Translation) {
            $propertyValue = (string)$val;
        } else {
            $propertyValue = $val;
        }

        $separator = $this->multipleSeparator();

        /** Parse multiple values / ensure they are of array type. */
        if ($this['multiple']) {
            if (!is_array($propertyValue)) {
                $propertyValue = explode($separator, $propertyValue);
            }
        }

        if ($separator === ',') {
            $separator = ', ';
        }

        if (is_array($propertyValue)) {
            foreach ($propertyValue as &$value) {
                if (is_string($value)) {
                    $value = $this->choiceLabel($value);
                    if (!is_string($value)) {
                        $value = $this->l10nVal($value, $options);
                    }
                }
            }
            $propertyValue = implode($separator, $propertyValue);
        } elseif (is_string($propertyValue)) {
            $propertyValue = $this->choiceLabel($propertyValue);
            if (!is_string($propertyValue)) {
                $propertyValue = $this->l10nVal($propertyValue, $options);
            }
        }

        return $propertyValue;
    }


    /**
     * Get the SQL type (Storage format). ISO 639-1 value is a 2-character language code.
     *
     * @see StorablePropertyTrait::sqlType()
     * @return string The SQL type
     */
    public function sqlType()
    {
        if ($this['multiple']) {
            return 'TEXT';
        }

        return 'CHAR(2)';
    }

    /**
     * @return integer
     */
    public function sqlPdoType()
    {
        return PDO::PARAM_STR;
    }
}
