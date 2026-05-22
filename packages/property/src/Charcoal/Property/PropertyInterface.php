<?php

declare(strict_types=1);

namespace Charcoal\Property;

/**
 *
 */
interface PropertyInterface
{
    /**
     * Get the "type" (identifier) of the property.
     */
    public function type(): string;

    /**
     * Set the property's identifier.
     *
     * @param string $ident The property identifier.
     */
    public function setIdent(string $ident): PropertyInterface;

    /**
     * Retrieve the property's identifier.
     */
    public function getIdent(): string;

    /**
     * Retrieve the property's localized identifier.
     *
     * @param string|null $lang The language code to return the identifier with.
     */
    public function l10nIdent(?string $lang = null): string;

    /**
     * Parse the given value.
     *
     * @param  mixed $val The value to be parsed (normalized).
     * @return mixed Returns the parsed value.
     */
    public function parseVal(mixed $val): mixed;

    /**
     * @param  mixed $val A single value to parse.
     * @return mixed The parsed value.
     */
    public function parseOne(mixed $val): mixed;

    /**
     * @param  mixed $val Optional. The value to to convert for input.
     */
    public function inputVal(mixed $val): string;

    /**
     * @param  mixed $val Optional. The value to to convert for display.
     */
    public function displayVal(mixed $val): string;

    /**
     * @param  mixed $label The property label.
     */
    public function setLabel(mixed $label): PropertyInterface;

    public function getLabel(): mixed;
    /**
     * @param boolean $l10n The l10n, or "translatable" flag.
     */
    public function setL10n(bool $l10n): PropertyInterface;
    public function getL10n(): bool;

    /**
     * @param boolean $hidden The hidden flag.
     * @return PropertyInterface Chainable
     */
    public function setHidden(bool $hidden): PropertyInterface;
    public function getHidden(): bool;

    /**
     * @param boolean $multiple The multiple flag.
     */
    public function setMultiple(bool $multiple): PropertyInterface;
    public function getMultiple(): bool;

    /**
     * Set the multiple options / configuration, when property is `multiple`.
     *
     * ## Options structure
     * - `separator` (string) The separator charactor.
     * - `min` (integer) The minimum number of values. (0 = no limit).
     * - `max` (integer) The maximum number of values. (0 = no limit).
     *
     * @param  array $multipleOptions The property multiple options.
     */
    public function setMultipleOptions(array $multipleOptions): PropertyInterface;
    /**
     * The options defining the property behavior when the multiple flag is set to true.
     *
     * @param  string|null $key Optional setting to retrieve from the options.
     */
    public function getMultipleOptions(?string $key = null): mixed;

    /**
     * @param boolean $allow The property allow null flag.
     */
    public function setAllowNull(bool $allow): PropertyInterface;
    public function getAllowNull(): bool;

    /**
     * @param boolean $required The property required flag.
     */
    public function setRequired(bool $required): PropertyInterface;
    public function getRequired(): bool;

    /**
     * @param boolean $unique The property unique flag.
     */
    public function setUnique(bool $unique): PropertyInterface;
    public function getUnique(): bool;

    /**
     * @param boolean $storable The property storable flag.
     */
    public function setStorable(bool $storable): PropertyInterface;
    public function getStorable(): bool;

    /**
     * @param boolean $active The property active flag. Inactive properties should have no effects.
     */
    public function setActive(bool $active): PropertyInterface;
    public function getActive(): bool;

    /**
     * @param  mixed $val The value, at time of saving.
     */
    public function save(mixed $val): mixed;
}
