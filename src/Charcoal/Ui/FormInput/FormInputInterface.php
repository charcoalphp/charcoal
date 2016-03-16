<?php

namespace Charcoal\Ui\FormInput;

// Intra-module (`charcoal-ui`) dependencies
use \Charcoal\Ui\FormGroup\FormGroupInterface;

/**
 * Form Input Interface
 */
interface FormInputInterface
{
    /**
     * @param FormGroupInterface $formGroup The parent form group object.
     * @return FormInputInterface Chainable
     */
    public function setFormGroup(FormGroupInterface $formGroup);
}
