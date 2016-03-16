<?php

namespace Charcoal\Ui\FormGroup;

use \InvalidArgumentException;

// Intra-module (`charcoal-ui`) dependencies
use \Charcoal\Ui\Form\FormInterface;
use \Charcoal\Ui\FormInput\FormInputBuilder;
use \Charcoal\Ui\FormInput\FormInputInterface;

/**
 *
 */
trait FormGroupTrait
{
     /**
      * @var FormInputInterface[] $inputs
      */
    private $inputs;

    /**
     * In-memory copy of the parent form widget.
     * @var FormInterface $form
     */
    protected $form;

    /**
     * @var FormInputBuilder $formInputBuilder
     */
    protected $formInputBuilder;

    /**
     * @var callable $itemCallback
     */
    private $inputCallback = null;

    /**
     * @param FormInputBuilder $builder
     * @return FormGroupInterface Chainable
     */
    protected function setFormInputBuilder(FormInputBuilder $builder)
    {
        $this->formInputBuilder = $builder;
        return $this;
    }

    /**
     * @param callable $cb
     * @return FormGroupInterface Chainable
     */
    public function setInputCallback(callable $cb)
    {
        $this->inputCallback = $cb;
        return $this;
    }

    /**
     * @param Form $form
     * @return FormGroupInterface Chainable
     */
    public function setForm(FormInterface $form)
    {
        $this->form = $form;
        return $this;
    }

    /**
     * @var integer $priority
     * @throws InvalidArgumentException
     * @return FormGroupInterface Chainable
     */
    public function setPriority($priority)
    {
        if (!is_numeric($priority)) {
            throw new InvalidArgumentException(
                'Priority must be an integer'
            );
        }
        $priority = (int)$priority;
        $this->priority = $priority;
        return $this;
    }

    /**
     * @return integer
     */
    public function priority()
    {
        return $this->priority;
    }

    /**
     * @param array $inputs
     * @return FormGroupInterface Chainable
     */
    public function setInputs(array $inputs)
    {
        $this->inputs = [];
        foreach ($inputs as $inputIdent => $input) {
            $this->addInput($inputIdent, $input);
        }
        return $this;
    }

    /**
     * @param string                   $propertyIdent
     * @param array|FormInputInterface
     * @throws InvalidArgumentException
     * @return FormInterface Chainable
     */
    public function addInput($inputIdent, $input)
    {
        if (!is_string($inputIdent)) {
            throw new InvalidArgumentException(
                'Group ident must be a string'
            );
        }

        if (($input instanceof FormInputInterface)) {
            $input->setForm($this->form);
            $input->setFormGroup($this);
            $this->inputs[$inputIdent] = $input;
        } elseif (is_array($input)) {
            $g = $this->formInputBuilder->build($input);
            $this->inputs[$inputIdent] = $g;
        } else {
            throw new InvalidArgumentException(
                'Group must be a Form Group object or an array of form group options'
            );
        }

        return $this;
    }

    /**
     * Form Input generator.
     *
     * @return FormGroupInterface[]
     */
    public function inputs(callable $inputCallback = null)
    {
        $groups = $this->groups;
        uasort($groups, ['self', 'sortInputsByPriority']);

        $groupCallback = isset($inputCallback) ? $inputCallback : $this->inputCallback;
        foreach ($inputs as $input) {
            if ($groupCallback) {
                $groupCallback($input);
            }
            $GLOBALS['widget_template'] = $input->template();
            yield $input->ident() => $input;
        }

    }

    /**
     * @return boolean
     */
    public function hasInputs()
    {
        return (count($this->inputs) > 0);
    }

    /**
     * @return integer
     */
    public function numInputs()
    {
        return count($this->inputs);
    }
}
