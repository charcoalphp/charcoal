<?php

namespace Charcoal\Ui\FormGroup;

use Charcoal\Ui\Form\FormInterface;

/**
 * Defines a form group.
 */
interface FormGroupInterface
{
    /**
     * @param callable $cb The input callback.
     * @return FormGroupInterface Chainable
     */
    public function setInputCallback(callable $cb);

    /**
     * Set the form widget.
     *
     * @param FormInterface $form The related form widget.
     * @return FormGroupInterface Chainable
     */
    public function setForm(FormInterface $form);

    /**
     * Retrieve the form widget.
     *
     * @return FormInterface
     */
    public function form();

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

    /**
     * Set the identifier of the group.
     *
     * @param string $ident Sidemenu group identifier.
     * @return FormGroupInterface Chainable
     */
    public function setIdent($ident);

    /**
     * Retrieve the idenfitier of the group.
     *
     * @return string
     */
    public function ident();
}
