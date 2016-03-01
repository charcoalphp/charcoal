<?php

namespace Charcoal\Ui\FormGroup;

use \Charcoal\Ui\UiItemInterface;
use \Charcoal\Ui\Form\FormInterface;

/**
 * Form Group Interface
 */
interface FormGroupInterface extends UiItemInterface
{
    /**
     * @param callable $cb
     * @return FormGroupInterface Chainable
     */
    public function setInputCallback(callable $cb);

    /**
     * @param Form $form
     * @return FormGroupInterface Chainable
     */
    public function setForm(FormInterface $form);

        /**
         * @var integer $priority
         * @throws InvalidArgumentException
         * @return FormGroupInterface Chainable
         */
    public function setPriority($priority);

    /**
     * @return integer
     */
    public function priority();

    /**
     * @param array $inputs
     * @return FormGroupInterface Chainable
     */
    public function setInputs(array $inputs);

    /**
     * @param string $propertyIdent
     * @param array|FormInputInterface
     * @throws InvalidArgumentException
     * @return FormInterface Chainable
     */
    public function addInput($inputIdent, $input);

    /**
     * Form Input generator
     *
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
