<?php

declare(strict_types=1);

namespace Charcoal\Admin\Property\Input;

// From 'charcoal-admin'
use Charcoal\Admin\Property\AbstractTickableInput;

/**
 * Checkbox Input Property
 *
 * The HTML _check box_ (`<input type="checkbox">`) input element represents
 * a control to select an array of different values.
 * — {@link https://developer.mozilla.org/en-US/docs/Web/HTML/Element/input/checkbox}
 *
 * This form control is similar to {@see RadioInput}.
 */
class CheckboxInput extends AbstractTickableInput
{
    /**
     * Retrieve the control type for the HTML element `<input>`.
     */
    public function type(): string
    {
        return 'checkbox';
    }

    /**
     * Always accept multiple values.
     */
    #[\Override]
    public function multiple(): bool
    {
        return true;
    }
}
