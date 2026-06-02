<?php

declare(strict_types=1);

namespace Charcoal\Admin\Property\Input;

use InvalidArgumentException;

/**
 * Readonly Select Options Input Property
 */
class ReadonlySelectInput extends SelectInput
{
    /**
     * Retrieve the selected value and display it.
     */
    public function displayVal(): string
    {
        $selectedChoices = [];

        foreach ($this->choices() as $choice) {
            if ($this->isChoiceSelected($choice)) {
                $selectedChoices[] = $choice['label'];
                // break;
            }
        }

        return implode(', ', $selectedChoices);
    }
}
