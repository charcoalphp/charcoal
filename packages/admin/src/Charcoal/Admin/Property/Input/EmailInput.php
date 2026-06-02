<?php

declare(strict_types=1);

namespace Charcoal\Admin\Property\Input;

use Charcoal\Admin\Property\Input\TextInput;

/**
 * Email Address Input
 */
class EmailInput extends TextInput
{
    /**
     * Retrieve the control type for the HTML element `<input>`.
     */
    #[\Override]
    public function type(): string
    {
        return 'email';
    }
}
