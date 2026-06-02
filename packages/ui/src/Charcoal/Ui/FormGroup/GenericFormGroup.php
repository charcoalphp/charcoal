<?php

declare(strict_types=1);

namespace Charcoal\Ui\FormGroup;

use Charcoal\Ui\FormGroup\AbstractFormGroup;

/**
 * A Generic Form Group
 *
 * Concreete implementation of {@see \Charcoal\Ui\FormGroup\FormGroupInterface}.
 */
class GenericFormGroup extends AbstractFormGroup
{
    /**
     * Retrieve the form group type.
     */
    #[\Override]
    public function type(): string
    {
        return 'charcoal/ui/form-group/generic';
    }
}
