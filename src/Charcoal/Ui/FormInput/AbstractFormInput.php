<?php

namespace Charcoal\Ui\FormInput;

// Intra-module (`charcoal-ui`) dependencies
use \Charcoal\Ui\AbstractUiItem;
use \Charcoal\Ui\FormGroup\FormGroupInterface;

/**
 *
 */
abstract class AbstractFormInput extends AbstractUiItem implements FormInputInterface
{
    /**
     * @var FormGroupInterface $formGroup
     */
    protected $formGroup;

    /**
     * @param FormGroupInterface $formGroup
     * @return FormInputInterface Chainable
     */
    public function setFormGroup(FormGroupInterface $formGroup)
    {
        $this->formGroup = $formGroup;
        return $this;
    }
}
