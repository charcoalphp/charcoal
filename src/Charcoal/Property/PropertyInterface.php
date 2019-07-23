<?php

namespace Charcoal\Property;

/**
 *
 */
interface PropertyInterface
{
    /**
     * Get the "type" (identifier) of the property.
     * @return string
     */
    public function type();

    /**
     * Set the property's identifier.
     *
     * @param  string $ident The property identifier.
     * @return PropertyInterface Chainable
     */
    public function setIdent($ident);

    /**
     * Retrieve the property's identifier.
     *
     * @return string
     */
    public function getIdent();

    /**
     * Retrieve the property's localized identifier.
     *
     * @param  string|null $lang The language code to return the identifier with.
     * @return string
     */
    public function l10nIdent($lang = null);

    /**
     * Parse the given value.
     *
     * @param  mixed $val The value to be parsed (normalized).
     * @throws \InvalidArgumentException If the value does not match property settings.
     * @return mixed Returns the parsed value.
     */
    public function parseVal($val);

    /**
     * @param mixed $val A single value to parse.
     * @return mixed The parsed value.
     */
    public function parseOne($val);

    /**
     * @param mixed $val Optional. The value to to convert for input.
     * @return string
     */
    public function inputVal($val);

    /**
     * @param mixed $val Optional. The value to to convert for display.
     * @return string
     */
    public function displayVal($val);

    /**
     * @param mixed $label The property label.
     * @return PropertyInterface Chainable
     */
    public function setLabel($label);

    /**
     * @return mixed
     */
    public function getLabel();

    /**
     * @param boolean $l10n The l10n, or "translatable" flag.
     * @return PropertyInterface Chainable
     */
    public function setL10n($l10n);

    /**
     * @return boolean
     */
    public function getL10n();

    /**
     * @param boolean $hidden The hidden flag.
     * @return PropertyInterface Chainable
     */
    public function setHidden($hidden);

    /**
     * @return boolean
     */
    public function getHidden();

    /**
     * @param boolean $multiple The multiple flag.
     * @return PropertyInterface Chainable
     */
    public function setMultiple($multiple);

    /**
     * @return boolean
     */
    public function getMultiple();

    /**
     * Set the multiple options / configuration, when property is `multiple`.
     *
     * ## Options structure
     * - `separator` (string) The separator charactor.
     * - `min` (integer) The minimum number of values. (0 = no limit).
     * - `max` (integer) The maximum number of values. (0 = no limit).
     *
     * @param array $multipleOptions The property multiple options.
     * @return PropertyInterface Chainable
     */
    public function setMultipleOptions(array $multipleOptions);

    /**
     * @return array
     */
    public function getMultipleOptions();

    /**
     * @param boolean $required The property required flag.
     * @return PropertyInterface Chainable
     */
    public function setRequired($required);

    /**
     * @return boolean
     */
    public function getRequired();

    /**
     * @param boolean $unique The property unique flag.
     * @return PropertyInterface Chainable
     */
    public function setUnique($unique);

    /**
     * @return boolean
     */
    public function getUnique();

    /**
     * @param boolean $storable The property storable flag.
     * @return PropertyInterface Chainable
     */
    public function setStorable($storable);

    /**
     * @return boolean
     */
    public function getStorable();

    /**
     * @param boolean $active The property active flag. Inactive properties should have no effects.
     * @return PropertyInterface Chainable
     */
    public function setActive($active);

    /**
     * @return boolean
     */
    public function getActive();

    /**
     * @param mixed $val The value, at time of saving.
     * @return mixed
     */
    public function save($val);
}
