<?php

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
     *
     * @return string
     */
    public function type()
    {
        return 'charcoal/ui/form-group/generic';
    }
}
