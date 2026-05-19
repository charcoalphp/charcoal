<?php

declare(strict_types=1);

namespace Charcoal\Admin\Property\Input\Selectize;

// From 'charcoal-admin'
use Charcoal\Admin\Property\Input\SelectizeInput;

/**
 * Listable Input Selectize
 */
class ListInput extends SelectizeInput
{
    #[\Override]
    public function inputType(): string
    {
        return 'charcoal/admin/property/input/selectize';
    }

    #[\Override]
    public function inputClass(): string
    {
        $parentClass = parent::inputClass();

        return $parentClass . ' selectize-list';
    }
}
