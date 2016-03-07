<?php

namespace Charcoal\Ui\FormGroup;

use \Charcoal\Ui\FormGroup\AbstractFormGroup;

/**
 * Generic, concrete FormGoup implementation.
 */
class GenericFormGroup extends AbstractFormGroup
{
    /**
     * @return string
     */
    public function type()
    {
        return 'charcoal/ui/form-group/generic';
    }
}
