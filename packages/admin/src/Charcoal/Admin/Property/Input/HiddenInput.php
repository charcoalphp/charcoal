<?php

declare(strict_types=1);

namespace Charcoal\Admin\Property\Input;

/**
 * Hidden Input Property
 */
class HiddenInput extends TextInput
{
    /**
     * Retrieve the control type for the HTML element `<input>`.
     */
    #[\Override]
    public function type(): string
    {
        return 'hidden';
    }
}
