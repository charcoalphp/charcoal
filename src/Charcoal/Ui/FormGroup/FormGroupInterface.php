<?php

namespace Charcoal\Ui\FormGroup;

use \Charcoal\Ui\Form\FormInterface;

/**
 * Form Group Interface
 */
interface FormGroupInterface
{
    /**
     * @param callable $cb The input callback.
     * @return FormGroupInterface Chainable
     */
    public function setInputCallback(callable $cb);

    /**
     * @param FormInterface $form The parent form object.
     * @return FormGroupInterface Chainable
     */
    public function setForm(FormInterface $form);

    /**
     * @param integer $priority Group priority, for sorting.
     * @return FormGroupInterface Chainable
     */
    public function setPriority($priority);

    /**
     * @return integer
     */
    public function priority();

    /**
     * @param string $mode The l10n mode.
     * @return FormGroupInterface Chainable
     */
    public function setL10nMode($mode);

    /**
     * @return string
     */
    public function l10nMode();

    /**
     * @param array $inputs The group inputs structure.
     * @return FormGroupInterface Chainable
     */
    public function setInputs(array $inputs);

    /**
     * @param string                   $inputIdent The input identifier.
     * @param array|FormInputInterface $input      The input object or structure.
     * @return FormInterface Chainable
     */
    public function addInput($inputIdent, $input);

    /**
     * Form Input generator
     *
     * @param callable $inputCallback Optional. Input callback.
     * @return FormGroupInterface[]
     */
    public function inputs(callable $inputCallback = null);

    /**
     * @return boolean
     */
    public function hasInputs();

    /**
     * @return integer
     */
    public function numInputs();
}
