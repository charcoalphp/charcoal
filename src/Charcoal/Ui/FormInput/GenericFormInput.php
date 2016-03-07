<?php

namespace Charcoal\Ui\FormInput;

use \Charcoal\Ui\FormInput\AbstractFormInput;

/**
 * Generic, concrete FormInput implementation.
 */
class GenericFormInput extends AbstractFormInput
{
    /**
     * @return string
     */
    public function type()
    {
        return 'charcoal/ui/form-input/generic';
    }
}
