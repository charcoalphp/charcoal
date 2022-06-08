<?php

namespace Charcoal\Ui\FormInput;

use Charcoal\Ui\FormInput\AbstractFormInput;

/**
 * A Generic Form Input
 *
 * Concreete implementation of {@see \Charcoal\Ui\FormInput\FormInputInterface}.
 */
class GenericFormInput extends AbstractFormInput
{
    /**
     * Retrieve the form input type.
     *
     * @return string
     */
    public function type()
    {
        return 'charcoal/ui/form-input/generic';
    }
}
