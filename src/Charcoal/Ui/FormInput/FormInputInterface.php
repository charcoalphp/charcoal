<?php

namespace Charcoal\Ui\FormInput;

// Intra-module (`charcoal-ui`) dependencies
use \Charcoal\Ui\UiItemInterface;
use \Charcoal\Ui\FormGroup\FormGroupInterface;

/**
 * Form Input Interface
 */
interface FormInputInterface extends UiItemInterface
{
    /**
     * @param FormGroupInterface $formGroup
     * @return FormInputInterface Chainable
     */
    public function setFormGroup(FormGroupInterface $formGroup);
}
