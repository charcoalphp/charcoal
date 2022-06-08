<?php

namespace Charcoal\Ui\FormInput;

// From 'charcoal-ui'
use Charcoal\Ui\FormGroup\FormGroupInterface;

/**
 * Defines a form input.
 */
interface FormInputInterface
{
    /**
     * Set the form input's parent group.
     *
     * @param FormGroupInterface $formGroup The parent form group object.
     * @return FormInputInterface Chainable
     */
    public function setFormGroup(FormGroupInterface $formGroup);

    /**
     * Retrieve the input's parent group.
     *
     * @return FormGroupInterface
     */
    public function formGroup();
}
