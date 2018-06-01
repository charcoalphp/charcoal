<?php

namespace Charcoal\Ui\Form;

// From 'charcoal-ui'
use Charcoal\Ui\Form\AbstractForm;

/**
 * A Generic Form
 *
 * Concreete implementation of {@see \Charcoal\Ui\Form\FormInterface}.
 */
class GenericForm extends AbstractForm
{
    /**
     * Retrieve the form type.
     *
     * @return string
     */
    public function type()
    {
        return 'charcoal/ui/form/generic';
    }
}
