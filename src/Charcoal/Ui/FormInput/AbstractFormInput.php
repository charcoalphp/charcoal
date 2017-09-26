<?php

namespace Charcoal\Ui\FormInput;

// Intra-module (`charcoal-ui`) dependencies
use Charcoal\Ui\AbstractUiItem;
use Charcoal\Ui\FormGroup\FormGroupInterface;

/**
 * A Basic Form Input
 *
 * Abstract implementation of {@see \Charcoal\Ui\FormInput\FormInputInterface}.
 */
abstract class AbstractFormInput extends AbstractUiItem implements
    FormInputInterface
{
    /**
     * The form group the input belongs to.
     *
     * @var FormGroupInterface
     */
    protected $formGroup;

    /**
     * Set the form input's parent group.
     *
     * @param FormGroupInterface $formGroup The parent form group object.
     * @return FormInputInterface Chainable
     */
    public function setFormGroup(FormGroupInterface $formGroup)
    {
        $this->formGroup = $formGroup;

        return $this;
    }

    /**
     * Retrieve the input's parent group.
     *
     * @return FormGroupInterface
     */
    public function formGroup()
    {
        return $this->formGroup;
    }
}
