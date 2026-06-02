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

    public function type(): string
    {
        return 'lang';
    }

    /**
     * Ensure the choices are never explicitly set, as they will always be auto-generated from environment / config.
     *
     * @param  array $choices One or more choice structures.
     * @see SelectablePropertyTrait::setChoices()
     */
    public function setChoices(array $choices): static
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
     */
    public function addChoices(array $choices): static
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
    public function addChoice($choiceIdent, $choice): static
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
     */
    public function hasChoices(): bool
    {
        return (bool)$this->translator()->locales();
    }

    /**
     * Determine if the given choice is available.
     *
     * @param  string $choiceIdent The choice identifier to lookup.
     * @see SelectablePropertyTrait::hasChoice()
     */
    public function hasChoice($choiceIdent): bool
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
                        $trans = 'locale.' . $langCode;
                        if ($trans === $this->translator()->translate($trans)) {
                            $label = strtoupper((string)$langCode);
                        } else {
                            $label = $this->translator()->translation($trans);
                        }
                    }

                    $choices[$langCode] = [
                        'label'    => $label,
                        'selected' => in_array($langCode, $selected),
                        'value'    => $langCode,
                    ];
                }

                $this->choices = $choices;
            }
        }

        return $this->choices;
    }

    /**
     * Format the given value for display.
     */
    #[\Override]
    public function displayVal(mixed $val, array $options = []): string
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

        /** Parse multiple values / ensure they are of array type. */
        if ($this['multiple'] && !is_array($propertyValue)) {
            $propertyValue = $this->parseValAsMultiple($propertyValue);
        }

        if (is_array($propertyValue)) {
            $separator = $this->multipleSeparator();
            if ($separator === ',') {
                $separator = ', ';
            }

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
    public function sqlType(): string
    {
        if ($this['multiple']) {
            return 'TEXT';
        }

        return 'CHAR(2)';
    }

    public function sqlPdoType(): int
    {
        return PDO::PARAM_STR;
    }
}
