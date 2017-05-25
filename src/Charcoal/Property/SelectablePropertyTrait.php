<?php

namespace Charcoal\Property;

use InvalidArgumentException;

// From 'charcoal-translator'
use Charcoal\Translator\Translation;

/**
 * Provides an implementation of {@see \Charcoal\Property\SelectablePropertyInterface}.
 */
trait SelectablePropertyTrait
{
    /**
     * The available selectable choices.
     *
     * @var array
     */
    protected $choices = [];

    /**
     * Set the available choices.
     *
     * @param  array $choices One or more choice structures.
     * @return SelectablePropertyInterface Chainable.
     */
    public function setChoices(array $choices)
    {
        $this->choices = [];

        $this->addChoices($choices);

        return $this;
    }

    /**
     * Merge the available choices.
     *
     * @param  array $choices One or more choice structures.
     * @return SelectablePropertyInterface Chainable.
     */
    public function addChoices(array $choices)
    {
        foreach ($choices as $choiceIdent => $choice) {
            $this->addChoice((string)$choiceIdent, $choice);
        }

        return $this;
    }

    /**
     * Add a choice to the available choices.
     *
     * @param  string       $choiceIdent The choice identifier (will be key / default ident).
     * @param  string|array $choice      A string representing the choice label or a structure.
     * @return SelectablePropertyInterface Chainable.
     */
    public function addChoice($choiceIdent, $choice)
    {
        $choice = $this->parseChoice($choice, (string)$choiceIdent);
        $choiceIdent = $choice['value'];

        $this->choices[$choiceIdent] = $choice;

        return $this;
    }

    /**
     * Determine if choices are available.
     *
     * @return boolean
     */
    public function hasChoices()
    {
        return !!$this->choices;
    }

    /**
     * Retrieve the available choice structures.
     *
     * @return array
     */
    public function choices()
    {
        return $this->choices;
    }

    /**
     * Determine if the given choice is available.
     *
     * @param  string $choiceIdent The choice identifier to lookup.
     * @return boolean
     */
    public function hasChoice($choiceIdent)
    {
        return isset($this->choices[$choiceIdent]);
    }

    /**
     * Retrieve the structure for a given choice.
     *
     * @param  string $choiceIdent The choice identifier to lookup.
     * @return mixed The matching choice.
     */
    public function choice($choiceIdent)
    {
        if (!isset($this->choices[$choiceIdent])) {
            return [
                'value' => $choiceIdent,
                'label' => ''
            ];
        }

        return $this->choices[$choiceIdent];
    }

    /**
     * Retrieve the label for a given choice.
     *
     * @param  string|array $choice The choice identifier to lookup.
     * @throws InvalidArgumentException If the choice is invalid.
     * @return string|null Returns the label. Otherwise, returns the raw value.
     */
    public function choiceLabel($choice)
    {
        if ($choice === null) {
            return null;
        }

        if (is_array($choice)) {
            if (isset($choice['label'])) {
                return $choice['label'];
            } elseif (isset($choice['value'])) {
                return $choice['value'];
            } else {
                throw new InvalidArgumentException(
                    'Choice structure must contain a "label" or "value".'
                );
            }
        }

        if (!is_string($choice)) {
            throw new InvalidArgumentException(
                'Choice identifier must be a string.'
            );
        }

        if ($this->hasChoice($choice)) {
            $choice = $this->choice($choice);
            return $choice['label'];
        } else {
            return $choice;
        }
    }

    /**
     * Parse the given values into choice structures.
     *
     * @param  array $choices One or more values to format.
     * @return array Returns a collection of choice structures.
     */
    protected function parseChoices(array $choices)
    {
        $parsed = [];
        foreach ($choices as $choiceIdent => $choice) {
            $choice = $this->parseChoice($choice, (string)$choiceIdent);
            $choiceIdent = $choice['value'];

            $parsed[$choiceIdent] = $choice;
        }

        return $parsed;
    }

    /**
     * Parse the given value into a choice structure.
     *
     * @param  string|array $choice      A string representing the choice label or a structure.
     * @param  string       $choiceIdent The choice identifier (will be key / default ident).
     * @throws InvalidArgumentException If the choice identifier is not a string.
     * @return array Returns a choice structure.
     */
    protected function parseChoice($choice, $choiceIdent)
    {
        if (!is_string($choiceIdent)) {
            throw new InvalidArgumentException(
                'Choice identifier must be a string.'
            );
        }

        if (is_string($choice) || $choice instanceof Translation) {
            $choice = [
                'value' => $choiceIdent,
                'label' => $this->translator()->translation($choice)
            ];
        } elseif (is_array($choice)) {
            if (!isset($choice['value'])) {
                $choice['value'] = $choiceIdent;
            }

            if (!isset($choice['label'])) {
                $choice['label'] = $this->translator()->translation($choice['value']);
            } elseif (!($choice['label'] instanceof Translation)) {
                $choice['label'] = $this->translator()->translation($choice['label']);
            }
        } else {
            throw new InvalidArgumentException(
                'Choice must be a string or an array.'
            );
        }

        return $choice;
    }

    /**
     * @return \Charcoal\Translator\Translator
     */
    abstract protected function translator();
}
