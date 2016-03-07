<?php

namespace Charcoal\Ui\Form;

use \Charcoal\Ui\Form\AbstractForm;

/**
 * Generic, concrete Form implementation.
 */
class GenericForm extends AbstractForm
{
    /**
     * @return string
     */
    public function type()
    {
        return 'charcoal/ui/form/generic';
    }
}
