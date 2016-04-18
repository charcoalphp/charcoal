<?php

namespace Charcoal\Property;

/**
 * Selectable properties provide choices.
 *
 * Choices are :
 * - `value`
 * - `label`
 * - `title`
 * - `subtext`
 * - `icon`
 * - `selected`
 */
interface SelectablePropertyInterface
{
    /**
     * Explicitely set the selectable choices (to the array map).
     *
     * @param array $choices The array of choice structures.
     * @return SelectablePropertyInterface Chainable.
     */
    public function setChoices(array $choices);

    /**
     * Add a choice to the available choices map.
     *
     * @param string       $choiceIdent The choice identifier (will be key / default ident).
     * @param string|array $choice      A string representing the choice label or a structure.
     * @return SelectablePropertyInterface Chainable.
     */
    public function addChoice($choiceIdent, $choice);

    /**
     * Get the choices array map.
     *
     * @return array
     */
    public function choices();

    /**
     * Returns wether a given choice_ident exists or not.
     *
     * @param string $choiceIdent The choice ident.
     * @return boolean True / false wether the choice exists or not.
     */
    public function hasChoice($choiceIdent);

    /**
     * Returns a choice structure for a given ident.
     *
     * @param string $choiceIdent The choice ident to load.
     * @return mixed The matching choice.
     */
    public function choice($choiceIdent);
}
