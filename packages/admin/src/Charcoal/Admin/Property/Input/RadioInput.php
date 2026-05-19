<?php

declare(strict_types=1);

namespace Charcoal\Admin\Property\Input;

// From 'charcoal-admin'
use Charcoal\Admin\Property\AbstractTickableInput;

/**
 * Radio Button Input Property
 *
 * The HTML _radio button_ (`<input type="radio">`) input element represents
 * a control to select a _single value_ from a list of different values.
 * — {@link https://www.w3.org/wiki/HTML/Elements/input/radio}
 *
 * This form control is similar to {@see CheckboxInput}.
 */
class RadioInput extends AbstractTickableInput
{
    /**
     * Retrieve the control type for the HTML element `<input>`.
     */
    public function type(): string
    {
        return 'radio';
    }

    /**
     * Never accept multiple values.
     */
    #[\Override]
    public function multiple(): bool
    {
        return false;
    }
}
