<?php

namespace Charcoal\Property;

/**
 * Defines a property providing options to choose from.
 */
interface SelectablePropertyInterface
{
    /**
     * Set the available choices.
     *
     * @param  array $choices One or more choice structures.
     * @return SelectablePropertyInterface Chainable.
     */
    public function setChoices(array $choices);

    /**
     * Merge the available choices.
     *
     * @param  array $choices One or more choice structures.
     * @return SelectablePropertyInterface Chainable.
     */
    public function addChoices(array $choices);

    /**
     * Add a choice to the available choices.
     *
     * @param string       $choiceIdent The choice identifier (will be key / default ident).
     * @param string|array $choice      A string representing the choice label or a structure.
     * @return SelectablePropertyInterface Chainable.
     */
    public function addChoice($choiceIdent, $choice);

    /**
     * Determine if choices are available.
     *
     * @return boolean
     */
    public function hasChoices();

    /**
     * Retrieve the available choice structures.
     *
     * @return array
     */
    public function choices();

    /**
     * Determine if the given choice is available.
     *
     * @param  string $choiceIdent The choice identifier to lookup.
     * @return boolean
     */
    public function hasChoice($choiceIdent);

    /**
     * Retrieve the structure for a given choice.
     *
     * @param  string $choiceIdent The choice identifier to lookup.
     * @return mixed The matching choice.
     */
    public function choice($choiceIdent);

    /**
     * Retrieve the label for a given choice.
     *
     * @param  mixed $choiceIdent The choice identifier to lookup.
     * @return string|null Returns the label. Otherwise, returns the raw value.
     */
    public function choiceLabel($choiceIdent);
}
