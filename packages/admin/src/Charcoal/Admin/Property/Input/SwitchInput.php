<?php

declare(strict_types=1);

namespace Charcoal\Admin\Property\Input;

// From 'charcoal-admin'
use Charcoal\Admin\Property\AbstractPropertyInput;

/**
 * Switch Input Property
 *
 * For displaying checkboxes and radio buttons as toggle switches.
 */
class SwitchInput extends AbstractPropertyInput
{
    /**
     * Retrieve the control type for the HTML element `<input>`.
     */
    public function type(): string
    {
        return 'checkbox';
    }

    public function checked(): bool
    {
        return (bool)$this->inputVal();
    }

    public function value(): int
    {
        return $this->inputVal() ? 1 : 0;
    }
}
