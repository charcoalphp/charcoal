<?php

namespace Charcoal\Property;

use \InvalidArgumentException;

/**
* Fully implements, as a Trait, the SelectablePropertyInterface.
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
     * Explicitely set the selectable choices (to the array map).
     *
     * @param array $choices The array of choice structures.
     * @return SelectablePropertyInterface Chainable.
     */
    public function setChoices(array $choices)
    {
        $this->choices = [];
        foreach ($choices as $choiceIdent => $choice) {
            $c = (string)$choiceIdent;
            $this->addChoice($c, $choice);
        }
        return $this;
    }

    /**
     * Add a choice to the available choices map.
     *
     * @param string       $choiceIdent The choice identifier (will be key / default ident).
     * @param string|array $choice      A string representing the choice label or a structure.
     * @throws InvalidArgumentException If the choice identifier is not a string.
     * @return SelectablePropertyInterface Chainable.
     */
    public function addChoice($choiceIdent, $choice)
    {
        if (!is_string($choiceIdent)) {
            throw new InvalidArgumentException(
                'Choice identifier must be a string.'
            );
        }

        if (!is_array($choice) && !is_string($choice)) {
            throw new InvalidArgumentException(
                'Choice must be a string or an array.'
            );
        }

        if (is_string($choice)) {
            $choice = [
                'value' => $choiceIdent,
                'label' => $this->translator()->translation($choice)
            ];
        } else {
            if (isset($choice['value'])) {
                $choiceIdent = (string)$choice['value'];
            } else {
                $choice['value'] = $choiceIdent;
            }

            if (isset($choice['label'])) {
                $choice['label'] = $this->translator()->translation($choice['label']);
            }
        }

        $this->choices[$choiceIdent] = $choice;

        return $this;
    }

    /**
     * Get the choices array map.
     *
     * @return array
     */
    public function choices()
    {
        return $this->choices;
    }

    /**
     * Returns wether a given choiceIdent exists or not.
     *
     * @param string $choiceIdent The choice ident.
     * @return boolean True / false wether the choice exists or not.
     */
    public function hasChoice($choiceIdent)
    {
        return isset($this->choices[$choiceIdent]);
    }

    /**
     * Returns a choice structure for a given ident.
     *
     * @param string $choiceIdent The choice ident to load.
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
     * @return \Charcoal\Translator\Translator
     */
    abstract protected function translator();
}
